@extends('admin_panel.layout.app')
@section('content')

<div class="content-wrapper">
    <div class="row">
        <!-- Add New Other Income -->
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0 text-white">Other Income</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Record independent miscellaneous income.</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form action="{{ route('other.income.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Income Source / Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Scrape sale, Return diff">
                        </div>
                        <div class="form-group mb-3">
                            <label>Deposit To (Account) <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-control" required>
                                <option value="">-- Choose Account --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') ? 'selected' : '' }}>
                                        {{ $acc->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Amount (PKR) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional details">
                        </div>
                        <button type="submit" class="btn btn-info w-100 py-2">Save Other Income</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- History & Edit/Delete -->
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Other Income</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center" id="datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Source</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incomes as $inc)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($inc->date)->format('d-m-Y') }}</td>
                                        <td>
                                            <strong>{{ $inc->title }}</strong><br>
                                            <small class="text-muted">{{ $inc->remarks }}</small>
                                        </td>
                                        <td class="text-info font-weight-bold">{{ number_format($inc->amount, 2) }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal{{ $inc->id }}">Edit</button>
                                            
                                            <!-- Delete Button -->
                                            <form action="{{ route('other.income.delete', $inc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this specific income?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $inc->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                      <div class="modal-dialog" role="document">
                                        <div class="modal-content text-left">
                                          <form action="{{ route('other.income.update', $inc->id) }}" method="POST">
                                              @csrf
                                              @method('PUT')
                                              <div class="modal-header">
                                                <h5 class="modal-title">Edit Other Income</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                                </button>
                                              </div>
                                              <div class="modal-body">
                                                  <div class="form-group mb-2">
                                                      <label>Date</label>
                                                      <input type="date" name="date" class="form-control" value="{{ $inc->date }}" required>
                                                  </div>
                                                  <div class="form-group mb-2">
                                                      <label>Source / Title</label>
                                                      <input type="text" name="title" class="form-control" value="{{ $inc->title }}" required>
                                                  </div>
                                                  <div class="form-group mb-2">
                                                      <label>Amount (PKR)</label>
                                                      <input type="number" name="amount" class="form-control" step="0.01" min="1" value="{{ $inc->amount }}" required>
                                                  </div>
                                                  <div class="form-group mb-2">
                                                      <label>Remarks</label>
                                                      <input type="text" name="remarks" class="form-control" value="{{ $inc->remarks }}">
                                                  </div>
                                              </div>
                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Income</button>
                                              </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        $('#datatable').DataTable();
    });
</script>
@endsection
@endsection
