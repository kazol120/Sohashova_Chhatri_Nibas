@extends('layouts.app')
@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <h5 class="card-header">Project List</h5>
    <div class="card-datatable text-nowrap">
      <!-- Add button to create new project -->
      <button type="button" class="btn btn-primary ms-5 mb-2" data-bs-toggle="modal" data-bs-target="#exampleModal">New Create</button>
      <table class="dt-scrollableTable table" id="projectTable">
        <thead>
          <tr>
            <th>Sl</th>
            <th>Project Name</th>
            <th>Client Name</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($projectlist as $key => $projectname)
          <tr>
            <td>{{ $loop->iteration }}</td> 
            <td>{{ $projectname->name }}</td>
            <td>{{ $projectname->client->name ?? 'N/A' }}</td>
            <td>
                   <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampledetail" onclick="detailmodal({{ json_encode($projectname) }})"><i class="fas fa-edit me-1"></i>Detail</button>


              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModedit" onclick="editmodal({{ json_encode($projectname) }})"><i class="fas fa-edit me-1"></i>Edit</button>
              <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModaldelete" onclick="deletemodal({{ json_encode($projectname) }})"><i class="fa fa-trash me-1"></i>Delete</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Project Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New Create</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="#" method="POST">
          @csrf
            @method('post')
          <div class="mb-3">
            <label for="projectname" class="form-label">Project Name</label>
            <input type="text" class="form-control" id="projectname" name="name" placeholder="Name">
          </div>
          <button type="submit" class="btn btn-primary" onclick="showToast()">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="exampleModedit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="#" method="POST" id="editForm">
          @csrf
          @method('PUT')
          <input type="hidden" name="idprojcet" id="projectdata_id">
          <div class="mb-3">
            <label for="name" class="form-label">Project Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Name">
          </div>
          <button type="submit" class="btn btn-primary" onclick="showToast()">Update</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- detail Project Modal -->
<div class="modal fade" id="exampledetail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="#" method="GET" id="detailform">
          @csrf
          @method('GET')
          <input type="" name="detailid" id="detailid">
          <h3>Desciption</h3>
             <textarea class="form-control" id="descrtiption" rows="4"></textarea>
          <button type="submit" class="btn btn-primary" onclick="showToast()">Update</button>
        </form>
      </div>
    </div>
  </div>
</div>




<!-- Delete Project Modal -->
<div class="modal fade" id="exampleModaldelete" tabindex="-1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel2">Delete Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="#" method="POST" id="deleteform">
          @csrf
          @method('DELETE')
          <p>Are you sure you want to delete this project?</p>
          <input type="hidden" name="projectdeleteid" id="projectdeleteid">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-danger" onclick="showToast()">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>



@endsection

<script src="https://code.jquery.com/jquery-3.6.3.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('#projectTable').DataTable();
  });

  function editmodal(projectname) {
    $('#name').val(projectname.name);
    $('#projectdata_id').val(projectname.id);
    $('#editForm').attr('action', `/project/${projectname.id}`);
  }

  function deletemodal(projectname) {
    $('#projectdeleteid').val(projectname.id);
     $('#descrtiption').text(projectname.description);
    $('#deleteform').attr('action', `/project/${projectname.id}`);
  }
  

  function detailmodal(projectname) {
    $('#detailid').val(projectname.id);
    $('#detailform').attr('action', `/project/${projectname.id}`);
  }

  function showToast() {
    const notifier = new AWN();
    notifier.success('Action completed successfully!', { durations: { success: 2000 } });
  }



</script>
