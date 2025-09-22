<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Department;
use App\Models\User;
use App\Services\NotifyRecipients;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function manage()
    {
        $this->authorize('viewAny', Announcement::class);
        $list = Announcement::query()->latest()->paginate(15);
        return view('announcements.manage', compact('list'));
    }

    public function create()
    {
        $this->authorize('create', Announcement::class);
        $roles = Role::orderBy('name')->pluck('name', 'name');          // ['Admin'=>'Admin', ...]
        $departments = Department::orderBy('name')->pluck('name', 'id'); // [1=>'Sistemas', ...]
        return view('announcements.create', compact('roles', 'departments'));
    }

    public function store(Request $r, NotifyRecipients $notify)
    {
        $this->authorize('create', Announcement::class);

        $data = $r->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'status'       => 'required|in:draft,published',
            'is_pinned'    => 'boolean',
            'starts_at'    => 'nullable|date',
            'ends_at'      => 'nullable|date|after_or_equal:starts_at',
            'audience'     => 'required|in:all,role,department',
            'audience_values'   => 'nullable|array',
            'audience_values.*' => 'required_if:audience,role,department',
        ]);

        $data['audience_values'] = $this->normalizeAudienceValues($r, $data['audience']);
        $data['author_id'] = $r->user()->id;

        $a = Announcement::create($data);

        // 🔔 Notificar solo si se publica
        if ($a->status === 'published') {
            // Delegamos a NotifyRecipients para decidir destinatarios (escalable)
            $notify->onAnnouncementCreated($a);
        }

        return redirect()->route('announcements.manage')->with('ok', 'Aviso guardado');
    }

    public function edit(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $roles = Role::orderBy('name')->pluck('name', 'name');
        $departments = Department::orderBy('name')->pluck('name', 'id');
        return view('announcements.edit', ['a' => $announcement, 'roles' => $roles, 'departments' => $departments]);
    }

    public function update(Request $r, Announcement $announcement, NotifyRecipients $notify)
    {
        $this->authorize('update', $announcement);

        $data = $r->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'status'       => 'required|in:draft,published',
            'is_pinned'    => 'boolean',
            'starts_at'    => 'nullable|date',
            'ends_at'      => 'nullable|date|after_or_equal:starts_at',
            'audience'     => 'required|in:all,role,department',
            'audience_values'   => 'nullable|array',
            'audience_values.*' => 'required_if:audience,role,department',
        ]);

        $data['audience_values'] = $this->normalizeAudienceValues($r, $data['audience']);

        $wasPublished = $announcement->status === 'published';

        $announcement->update($data);

        // 🔔 Si pasa de borrador a publicado, notificar
        if (!$wasPublished && $announcement->status === 'published') {
            $notify->onAnnouncementCreated($announcement);
        }

        return redirect()->route('announcements.manage')->with('ok', 'Aviso actualizado');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);
        $announcement->delete();
        return back()->with('ok', 'Aviso eliminado');
    }

    // Feed para dashboard
    public function feed()
    {
        $items = Announcement::visibleTo(auth()->user())
            ->orderByDesc('is_pinned')->latest()->limit(10)->get();
        return view('announcements.feed', compact('items'));
    }

    // Marcar como leído
    public function markRead(Announcement $announcement)
    {
        AnnouncementRead::updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => auth()->id()],
            ['read_at' => now()]
        );
        return response()->noContent();
    }

    // --- Helpers ---
    protected function normalizeAudienceValues(Request $r, string $audience): ?array
    {
        if ($audience === 'all') {
            return null;
        }

        $vals = $r->input('audience_values'); // array o "Admin,Rector"
        if (is_string($vals)) {
            $vals = array_values(array_filter(array_map('trim', explode(',', $vals))));
        } elseif (is_array($vals) && count($vals) === 1 && is_string($vals[0]) && str_contains($vals[0], ',')) {
            $vals = array_values(array_filter(array_map('trim', explode(',', $vals[0]))));
        }

        if ($audience === 'department' && is_array($vals)) {
            $vals = array_map(fn($v) => (int)$v, $vals);
        }

        return $vals ?: null;
    }

    protected function audienceUsers(string $audience, ?array $values)
    {
        // (Con NotifyRecipients ya no se usa para notificar, pero puedes mantenerlo para otras vistas)
        $q = User::query();

        return match ($audience) {
            'all'        => $q->get(),
            'role'       => $q->whereHas('roles', fn($r) => $r->whereIn('name', $values ?? []))->get(),
            'department' => $q->whereIn('department_id', $values ?? [])->get(),
            default      => collect(),
        };
    }
}
