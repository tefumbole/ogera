<?php

namespace App\Http\Controllers;

use App\Services\BeyondWasenderService;
use App\StaffPermission;
use App\Support\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Role;

class StaffPermissionAdminController extends Controller
{
    protected $all_permission = [];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $role = Role::find(Auth::user()->role_id);
                if ($role) {
                    foreach (Role::findByName($role->name)->permissions as $permission) {
                        $this->all_permission[] = $permission->name;
                    }
                }
            }
            View::share('all_permission', $this->all_permission);

            return $next($request);
        });
    }

    protected function authorizePerms()
    {
        if (in_array('permissions_module', $this->all_permission, true)
            || in_array('permissions.view', $this->all_permission, true)
            || in_array('permissions.manage', $this->all_permission, true)
            || in_array('hrm', $this->all_permission, true)) {
            return;
        }
        abort(403, 'You are not allowed to access Permissions.');
    }

    public function requests(Request $request)
    {
        return $this->list($request, StaffPermission::STATUS_PENDING, 'Permission Requests', 'permissions.requests');
    }

    public function approved(Request $request)
    {
        return $this->list($request, StaffPermission::STATUS_APPROVED, 'Approved Permissions', 'permissions.approved');
    }

    public function index(Request $request)
    {
        return $this->list($request, 'all', 'Permissions Listings', 'permissions.index');
    }

    protected function list(Request $request, $status, $title, $tab)
    {
        $this->authorizePerms();
        $q = StaffPermission::orderByDesc('created_at');
        if ($status !== 'all') {
            $q->where('status', $status);
        }
        if ($request->get('q')) {
            $search = $request->get('q');
            $q->where(function ($w) use ($search) {
                $w->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_role', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        return view('staff_permissions.index', [
            'items' => $q->paginate(40),
            'pageTitle' => $title,
            'statusFilter' => $status,
            'q' => $request->get('q'),
            'permTab' => $tab,
        ]);
    }

    public function update(Request $request, $id, BeyondWasenderService $whatsapp)
    {
        $this->authorizePerms();
        if (! in_array('permissions.manage', $this->all_permission, true)
            && ! in_array('permissions_module', $this->all_permission, true)
            && ! in_array('hrm', $this->all_permission, true)) {
            abort(403);
        }

        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $item = StaffPermission::findOrFail($id);
        $previous = $item->status;
        $item->status = $data['status'];
        $item->admin_note = $data['admin_note'] ?? $item->admin_note;
        if ($data['status'] !== StaffPermission::STATUS_PENDING) {
            $item->reviewed_by = Auth::id();
            $item->reviewed_at = now();
        }
        $item->save();

        if ($previous !== $item->status && $item->phone) {
            try {
                $heading = $item->status === StaffPermission::STATUS_APPROVED ? 'Permission Approved' : 'Permission Update';
                $emoji = $item->status === StaffPermission::STATUS_APPROVED ? '✅' : 'ℹ️';
                $msg = WhatsAppMessage::statusBlock($emoji, $heading)
                    .WhatsAppMessage::greeting($item->full_name)
                    ."Your permission request *{$item->reference_number}* is now *{$item->status}*.\n\n"
                    .WhatsAppMessage::bullet('From', $item->from_at->format('Y-m-d H:i'))
                    .WhatsAppMessage::bullet('To', $item->to_at->format('Y-m-d H:i'));
                if ($item->admin_note) {
                    $msg .= WhatsAppMessage::bullet('Note', $item->admin_note);
                }
                $msg .= WhatsAppMessage::footer();
                $whatsapp->sendText($item->phone, $msg);
            } catch (\Throwable $e) {
                Log::warning('Permission status WhatsApp failed: '.$e->getMessage());
            }
        }

        return back()->with('message', 'Permission updated.');
    }
}
