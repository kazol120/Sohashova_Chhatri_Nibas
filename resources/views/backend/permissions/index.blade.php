@extends('backend.layouts.app')
@section("title") | {{$page_title}} @endsection

@push('style')
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="{{asset('backend')}}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@endpush
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <!-- Title -->
                <h5 class="card-title mb-0">{{$page_title}}</h5>

                @can('permission-create')
                <!-- Button Group -->
                <div class="dt-action-buttons">
                    <div class="dt-buttons btn-group">
                        <!-- Create Button -->
                        <a href="{{route('permission.create')}}" class="btn btn-primary create-new waves-effect waves-light">
                            <span>
                                <i class="ti ti-plus me-1"></i>
                                <span class="d-none d-sm-inline-block">Add New</span>
                            </span>
                        </a>
                    </div>
                </div>
                @endcan
            </div>

            <div class="card-datatable text-nowrap">
                <table class="table" id="datatable">
                    <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Permission</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($permissions as $permission)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$permission->name}}</td>
                            <td>
                                @can('permission-edit')
                                    <a href="{{route('permission.edit',$permission)}}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('permission-delete')
                                    <button type="button"  data-id="{{ $permission->id }}" class="btn btn-danger delete_button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    <!-- Add more rows as required -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    Are you sure you want to proceed with this action?
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <form action="{{ route('permission.destroy', 0) }}" method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" id="delete_id" class="delete_id" value="0">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn btn-danger deleteButton"><i class="fa fa-trash"></i> DELETE</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script>
    $(document).ready(function () {
        $('#datatable').DataTable({
            dom: 'Bfrtip', // Defines the layout of DataTable
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-outline-info btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Export PDF',
                    className: 'btn btn-outline-danger btn-sm'
                }
            ],
            responsive: true, // Enables responsive layout for mobile devices
            paging: true, // Enables pagination
            ordering: true, // Enables column sorting
            info: true // Displays table information
        });
    });
</script>
<script>
    $(document).ready(function () {
        $(document).on("click", '.delete_button', function (e) {
            var id = $(this).data('id');
            var url = '{{ route("permission.destroy",":id") }}';
            url = url.replace(':id',id);
            $("#deleteForm").attr("action",url);
            $("#delete_id").val(id);
        });
    });
</script>
@endpush
