@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid" style="max-width:1100px;margin:0 auto;">
        <div class="mb-3">
            <h3 style="color:#0b3f90;font-weight:800;">{{ $pageTitle }}</h3>
            <p class="text-muted mb-0">Staff permission requests from the public Permissions page.</p>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <form method="GET" class="card card-body mb-3">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label>Search</label>
                    <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Name, role, reference…">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>

        <div class="card card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Role</th>
                            <th>Period</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th class="text-right">Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->full_name }}</strong><br>
                                    <span class="text-muted small">{{ $item->email }} · {{ $item->phone }}</span>
                                    @if($item->reason)<br><span class="small">{{ \Illuminate\Support\Str::limit($item->reason, 80) }}</span>@endif
                                </td>
                                <td>{{ $item->company_role }}</td>
                                <td class="small">
                                    {{ $item->from_at ? $item->from_at->format('M j, Y H:i') : '—' }}<br>
                                    → {{ $item->to_at ? $item->to_at->format('M j, Y H:i') : '—' }}
                                </td>
                                <td><code>{{ $item->reference_number }}</code></td>
                                <td><span class="badge badge-secondary">{{ $item->statusLabel() }}</span></td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('permissions.update', $item->id) }}" class="d-inline-flex flex-column align-items-end" style="gap:6px;min-width:170px;">
                                        @csrf
                                        <select name="status" class="form-control form-control-sm">
                                            @foreach(['pending','approved','rejected'] as $st)
                                                <option value="{{ $st }}" @if($item->status===$st) selected @endif>{{ ucfirst($st) }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="admin_note" class="form-control form-control-sm" placeholder="Note" value="{{ $item->admin_note }}">
                                        <button class="btn btn-sm btn-primary">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No permission records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($items, 'links'))
                <div class="mt-3">{{ $items->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
