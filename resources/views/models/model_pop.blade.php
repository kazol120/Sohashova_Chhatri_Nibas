@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row g-6 mb-6">
    <!--project wish utility -->
    <div class="col-sm-6 col-xl-2">
      <div class="card">
       <div class="card">
        <button
        type="button"
        class="btn btn-primary p-4"
        data-bs-toggle="modal"
        data-bs-target="#projectWishUtility">
        project t  ower model
      </button>
    </div>
  </div>
</div>
<!--provious money peceipt -->
<div class="col-sm-6 col-xl-2">
  <div class="card">
    <div class="card">
      <button
      type="button"
      class="btn btn-primary p-4"
      data-bs-toggle="modal"
      data-bs-target="#provious_money_peceipt">
      provious money peceipt
    </button>
  </div>
</div>
</div>
<!--- project wish service charge -->
<div class="col-sm-6 col-xl-2">
  <div class="card">
    <div class="card">
      <button
      type="button"
      class="btn btn-primary p-4"
      data-bs-toggle="modal"
      data-bs-target="#project_wish_service_charge_model">
      project_wish_service_charge
    </button>
  </div>
</div>
</div>
<!-- Bte- Associates-->
<div class="col-sm-6 col-xl-2">
  <div class="card">
   <div class="card">
    <button
    type="button"
    class="btn btn-primary p-4"
    data-bs-toggle="modal"
    data-bs-target="#bteassociates">
    Bte Associates
  </button>
</div>
</div>
</div>
</div>




<!-- projectWishUtility -->
<div class="modal fade " id="projectWishUtility" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-projectWishUtility">
    <div class="modal-content container-fluid ">
      <div class="modal-body">
       <h2  class="fs-4 mb-5" >PROJECT NAME:EVERGREEN MEHER TOWER (MOO3), OCTOBER 2024</h2>
       <button class="btn btn-info waves-effect waves-light ms-auto d-flex mb-3 fs-13">View Report</button>
       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       <table class="table table-bordered projecttowermodel">
        <thead>
          <tr>
            <th>SL</th>
            <th>ClientId</th>
            <th>ClientName</th>
            <th>Electrity</th>
            <th>Water</th>
            <th>GeneratorFull</th>
            <th>Gas</th>
            <th>Onthers</th>
            <th>TotalAmount</th>
            <th>Due</th>
            <th>View</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>C-10222</td>
            <td>smill food product</td>
            <td>457852</td>
            <td>0.366</td>
            <td>00</td>
            <td>00</td>
            <td>94.2546545</td>
            <td>542.214</td>
            <td>oo</td>
            <td><i class="fas fa-tv fa-2x"></i></td>
          </tr>
          <tr>
            <td>1</td>
            <td>C-10222</td>
            <td>smill food product</td>
            <td>457852</td>
            <td>0.366</td>
            <td>00</td>
            <td>00</td>
            <td>94.2546545</td>
            <td>542.214</td>
            <td>oo</td>
            <td><i class="fas fa-tv fa-2x"></i></td>
          </tr>
          <tr>
            <td>1</td>
            <td>C-10222</td>
            <td>smill food product</td>
            <td>457852</td>
            <td>0.366</td>
            <td>00</td>
            <td>00</td>
            <td>94.2546545</td>
            <td>542.214</td>
            <td>oo</td>
            <td><i class="fas fa-tv fa-2x"></i></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>


<!--provious_money_peceipt -->
<div class="modal fade " id="provious_money_peceipt" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-provious_money_peceipt">
    <div class="modal-content container-fluid ">
      <div class="modal-body">
        <div class="d-flex mb-5">
          <h2 class=" fs-4" >PROJECT NAME:EVERGREEN MEHER TOWER (MOO3), OCTOBER 2024</h2>
          <a href="" class="fa-solid fa-x ms-auto mt-4"></a>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <table class="table table-bordered provious_money_peceipt_bable">
          <thead>
            <tr>
              <th>Project Name</th>
              <th>Client name</th>
              <th>MRNo</th>
              <th>Particulars</th>
              <th>Inv Amount</th>
              <th>AiT</th>
              <th>VAT</th>
              <th>Less</th>
              <th>Previous Col.</th>
              <th>Receivable</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Evergreen meher tower</td>
              <td>kavazo</td>
              <td>32962</td>
              <td>Eletricity</td>
              <td>4888832</td>
              <td>0</td>
              <td>0</td>
              <td>0</td>
              <td>0</td>
              <td>456554</td>
              <td>4578552114</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>




<!-- project_wish_service_charge_model -->
<div class="modal fade " id="project_wish_service_charge_model" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-project_wish_service_charge_model">
    <div class="modal-content container-fluid ">
      <div class="modal-body">
       <h2 class="fs-4 mb-5" >PROJECT NAME:EVERGREEN MEHER TOWER (MOO3), january, 2024</h2>
       <button class="btn btn-info waves-effect waves-light ms-auto d-flex mb-3 fs-13">view Report</button>
       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       <table class="table table-bordered projecttowermodel">
        <thead>
          <tr>
            <th>SL</th>
            <th>ClientId</th>
            <th>ClientName</th>
            <th>No.Unit</th>
            <th>Area</th>
            <th>Inv Amount</th>
            <th>VAT</th>
            <th>AiT</th>
            <th>Collection</th>
            <th>Due</th>
            <th>View</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>C-10222</td>
            <td>Kavazo</td>
            <td>457852</td>
            <td>0.366</td>
            <td>00</td>
            <td>00</td>
            <td>94.2546545</td>
            <td>542.214</td>
            <td>oo</td>
            <td><i class="fas fa-tv fa-2x"></i></td>
          </tr>
          <tr>
            <td>1</td>
            <td>C-10222</td>
            <td>Kavazo</td>
            <td>457852</td>
            <td>0.366</td>
            <td>00</td>
            <td>00</td>
            <td>94.2546545</td>
            <td>542.214</td>
            <td>oo</td>
            <td><i class="fas fa-tv fa-2x"></i></td>
          </tr>
          <tr>
            <td>1</td>
            <td>C-10222</td>
            <td>Kavazo</td>
            <td>457852</td>
            <td>0.366</td>
            <td>00</td>
            <td>00</td>
            <td>94.2546545</td>
            <td>542.214</td>
            <td>oo</td>
            <td><i class="fas fa-tv fa-2x"></i></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>




<!-- Bte associates -->
<div class="modal fade " id="bteassociates" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-bteassociates">
    <div class="modal-content container-fluid ">
      <div class="modal-body">
       <h2  class="fs-4 mb-5" >PROJECT NAME:EVERGREEN MEHER TOWER (MOO3), C-00320, BTE Associates</h2>
       <button class="btn btn-info waves-effect waves-light ms-auto d-flex mb-3 fs-13">View Report</button>
       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       <table class="table table-bordered ">
        <thead>
          <tr>
            <th>SL</th>
            <th>Month</th>
            <th>Year</th>
            <th>No.Unit</th>
            <th>Area</th>
            <th>Inv Amount</th>
            <th>VAT</th>
            <th>AIT</th>
            <th>Calection</th>
            <th>Due</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>january</td>
            <td>2024</td>
            <td>1</td>
            <td>2577</td>
            <td>30952</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>30924</td>
          </tr>

           <tr>
            <td>1</td>
            <td>january</td>
            <td>2024</td>
            <td>1</td>
            <td>2577</td>
            <td>30952</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>30924</td>
          </tr>
          
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>




@endsection