@php
    $appSettings = \App\Services\SettingService::getSettingContentBySlug('app_setting');
    $devFeeActive = ($appSettings['development_fee_status'] ?? '1') == '1';
    $devFeeAmount = (float) ($appSettings['development_fee'] ?? 3000);
    $bookingButtonActive = ($appSettings['booking_button_status'] ?? '1') == '1';
@endphp
<style>
  /* ====== Room UI ====== */
  .room-box{
    width: 78px;
    height: 56px;
    border-radius: 14px;
    border: 2px solid #bcd8f5;
    background: #eef5fc;
    color: #033364;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    cursor: pointer;
    user-select: none;
    transition: transform .08s ease, background .15s ease, border-color .15s ease;
    position: relative;
    overflow: visible;
    font-size: 11px;
    padding: 0 4px;
  }

  .double-tag {
    display: inline-flex;
    align-items: center;
    font-size: 9px;
    font-weight: 700;
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
    border-radius: 4px;
    padding: 1px 3px;
    margin-right: 3px;
    line-height: 1.1;
  }

  .room-box:hover{
    transform: translateY(-1px);
    border-color: #033364;
    background: #d4e7fa;
  }

  /* Selected (Green) */
  .room-box.is-selected{
    background: #033364;     
    border-color: #033364;
    color: #fff;
  }

  .room-box.is-disabled{
    background: #ffe3e3;
    border-color: #ffa8a8;
    color: #e03131;
    opacity: 0.85;
    cursor: not-allowed;
  }

  .room-box.is-disabled:hover .room-hover-info {
    background: #ffffff !important;
    border: 1.5px solid #ffa8a8 !important;
  }

  /* Pending Approval (Soft Orange/Yellow) */
  .room-box.is-pending {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #b45309;
    cursor: pointer;
  }
  .room-box.is-pending:hover {
    background: #fde68a;
    border-color: #d97706;
  }

  .room-box.is-disabled:hover .room-hover-info::after {
    border-color: #ffffff transparent transparent transparent !important;
  }

  .hiddendisplay{
    display:none;
  }

  /* ===== Hover Info ===== */
  .room-hover-info{
    position: absolute;
    left: 50%;
    bottom: calc(100% + 10px);
    transform: translateX(-50%);
    min-width: 130px;
    background: #033364;
    color: #fff;
    padding: 8px 10px;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.45;
    text-align: left;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    z-index: 9999;
    box-shadow: 0 10px 24px rgba(0,0,0,.18);
    transition: opacity .18s ease, visibility .18s ease, transform .18s ease;
  }

  .room-hover-info::after{
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-width: 6px;
    border-style: solid;
    border-color: rgba(0,0,0,0.88) transparent transparent transparent;
  }

  .room-box:hover .room-hover-info{
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(-2px);
  }

  .room-hover-info strong{
    font-weight: 700;
  }

  /* ===== Total Amount Right Side ===== */
  .payment-head-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:12px;
    flex-wrap:wrap;
  }

  .payment-head-row .bk-title{
    margin-bottom:0;
  }

  .total-amountsss{
    margin-left:auto;
    text-align:right;
    font-weight:700;
    line-height:1.2;
  }

  .total-amountsss .amount-label{
    display:block;
    font-size:13px;
    color:#6b7280;
    margin-bottom:2px;
  }

  .total-amountsss .amouont{
    font-size:24px;
    font-weight:800;
    color:#033364;
  }

  @media (max-width: 576px){
    .payment-head-row{
      align-items:flex-start;
    }
    .total-amountsss{
      width:100%;
      text-align:right;
    }
    .total-amountsss .amouont{
      font-size:22px;
    }
  }

  /* ===== FLOATING BUTTON ===== */
  .floating-support {
    position: fixed;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1030;
    transition: opacity 0.25s ease, visibility 0.25s ease;
  }
  .support-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    border: none;
    padding: 14px 18px;
    background: linear-gradient(135deg, #033364, #054a8e);
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    box-shadow: 0 10px 25px rgba(0, 0, 0, .2);
    cursor: pointer;
    transition: all .3s ease;
    border-radius: 12px 0 0 12px;
  }
  .support-btn i {
    font-size: 20px;
  }
  .support-btn:hover {
    transform: translateX(-6px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, .25);
    background: linear-gradient(135deg, #022448, #033364);
  }

  /* ===== OFFCANVAS & SCROLL FIX ===== */
  .booking-canvas,
  .offcanvas.offcanvas-end.booking-canvas {
    overflow: hidden;
    width: 420px !important;
  }
  .booking-canvas-head {
    position: sticky;
    top: 0;
    z-index: 50;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    flex-shrink: 0;
  }
  .booking-canvas-body {
    padding: 0 !important;
  }
  #seatGridWrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 78px);
    overflow: hidden;
    box-sizing: border-box;
  }
  #seatGridWrap .bk-block {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    padding: 18px 12px 20px !important;
    box-sizing: border-box;
  }
  .seat-grid-card {
    width: 100%;
    padding: 20px 10px 10px !important;
    border-radius: 16px;
  }

  /* ===== STICKY BOOKING BAR ===== */
  .bk-sticky {
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 30;
    flex-shrink: 0;
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #033364;
    color: #fff;
    border-radius: 18px 18px 0 0;
    padding: 14px 16px;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, .15);
    margin: 0 !important;
  }
  .bk-sticky-left {
    min-width: 0;
    flex: 1 1 auto;
    overflow: hidden;
  }
  .bk-sticky-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 0 0 auto;
  }
  .bk-count {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
  }
  .bk-sub {
    font-size: 11px;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
    display: block;
    opacity: 0.85;
  }
  .bk-total {
    font-size: 16px;
    font-weight: 800;
    white-space: nowrap;
    text-align: right;
  }
  .bk-btn {
    border: none;
    min-width: 90px;
    padding: 8px 14px;
    border-radius: 10px;
    background: #fff;
    color: #033364;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease;
  }
  .bk-btn:hover:not(:disabled) {
    background: #eef5fc;
  }
  .bk-btn:disabled {
    opacity: .5;
    cursor: not-allowed;
  }
</style>

@if($bookingButtonActive)
<!-- Floating Book Room button -->
<div class="floating-support">
    <button type="button" class="support-btn" onclick="openroombookingModal()">
        <i class="bi bi-calendar-check"></i>
        <span>Book Room</span>
    </button>
</div>
@endif

<!-- ===================== BOOKING LEFT OFFCANVAS ===================== -->
<div id="bookingCanvas" class="offcanvas offcanvas-end booking-canvas" tabindex="-1" aria-labelledby="bookingCanvasLabel">
  <div class="offcanvas-header booking-canvas-head">
    <div>
      <h5 class="offcanvas-title m-0" id="bookingCanvasLabel">
        <i class="bi bi-journal-check"></i> Confirm Booking 
      </h5>
      <div class="small text-muted">Select room(s) → see total → click Booking</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <!-- ===================== ROOM GRID (MULTI SELECT) ===================== -->
  <div class="offcanvas-body booking-canvas-body bk-seat-center" id="seatGridWrap">
    <div class="bk-block mb-3 w-100">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-12">
            <div class="seat-grid-card">
              <div class="seat-wrapper">
                <div class="col-12 text-center d-flex flex-wrap justify-content-center gap-3">
                  @if(isset($rooms) && count($rooms) > 0)
                    @php
                      $groupedRoomsByFloor = $rooms->groupBy(function($r) {
                          return $r->floor->name ?? 'Unknown Floor';
                      });
                    @endphp
                    @foreach($groupedRoomsByFloor as $floorName => $floorRooms)
                      <div class="w-100 mb-2 mt-3 floor-header-section" data-floor-name="{{ $floorName }}">
                        <h6 class="fw-bold text-center mb-2" style="color: #033364; border-bottom: 1px dashed #bcd8f5; padding-bottom: 6px;">
                          <i class="bi bi-layers-half me-1"></i> {{ $floorName }}
                        </h6>
                      </div>
                      @php
                        $seatsCollection = collect();
                        foreach($floorRooms as $room) {
                            foreach($room->seats as $seat) {
                                $seatsCollection->push(['room' => $room, 'seat' => $seat]);
                            }
                        }
                        $groupedSections = [];
                        foreach($seatsCollection as $item) {
                            $rNo = (string)($item['room']->room_no ?? '');
                            if (preg_match('/^([A-Za-z]+)[\-_]/', $rNo, $m)) {
                                $sectionKey = 'Block ' . strtoupper($m[1]);
                            } elseif (preg_match('/(Block[\-_ ]?[A-Za-z])/i', $rNo, $m)) {
                                $sectionKey = ucwords(str_replace(['_', '-'], ' ', $m[1]));
                            } else {
                                $sectionKey = 'Block A';
                            }
                            $groupedSections[$sectionKey][] = $item;
                        }
                        uksort($groupedSections, function($a, $b) {
                            return strnatcasecmp($a, $b);
                        });
                        $seatSections = collect($groupedSections)->map(function($items) {
                            return collect($items)->sort(function($a, $b) {
                                $rNoA = (string)($a['room']->room_no ?? '');
                                $rNoB = (string)($b['room']->room_no ?? '');
                                
                                preg_match('/^([A-Za-z]+)[\-_](\d+)$/', $rNoA, $mA);
                                preg_match('/^([A-Za-z]+)[\-_](\d+)$/', $rNoB, $mB);
                                
                                $numA = isset($mA[2]) ? (int)$mA[2] : 0;
                                $numB = isset($mB[2]) ? (int)$mB[2] : 0;
                                
                                if ($numA !== $numB) {
                                    return $numA <=> $numB;
                                }
                                
                                $sA = (string)($a['seat']->seat_no ?? '');
                                $sB = (string)($b['seat']->seat_no ?? '');
                                return strnatcasecmp($sA, $sB);
                            });
                        });
                      @endphp
                      @foreach($seatSections as $sectionName => $secSeats)
                        @if($secSeats->count() > 0)
                          <div class="w-100 mb-2 mt-2">
                            <h6 class="fw-bold text-center text-secondary mb-1" style="font-size: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px;">{{ $sectionName }}</h6>
                          </div>
                          @foreach($secSeats as $item)
                            @php
                              $room = $item['room'];
                              $seat = $item['seat'];
                              $seatUniqueName = $room->room_no . '-' . $seat->seat_no;
                              $isSeatBooked = (isset($bookedRoomNumbers) && in_array($seatUniqueName, $bookedRoomNumbers)) || $seat->status == 1;
                              
                              $rNo = (string)($room->room_no ?? '');
                              $blockLetter = 'A';
                              $roomNum = 1;
                              if (preg_match('/^([A-Za-z]+)[\-_](\d+)$/', $rNo, $m)) {
                                  $blockLetter = strtoupper($m[1]);
                                  $roomNum = (int)$m[2];
                              }
                              
                              $seatCount = $room->seats->count();
                              if ($seatCount > 1) {
                                  $sNo = (string)($seat->seat_no ?? '');
                                  $seatSuffix = '1';
                                  if (preg_match('/(\d+)$/', $sNo, $sm)) {
                                      $seatSuffix = $sm[1];
                                  } elseif (str_contains(strtolower($sNo), '2') || str_contains(strtolower($sNo), 'b')) {
                                      $seatSuffix = '2';
                                  }
                                  $displaySeatLabel = "{$roomNum}-Block {$blockLetter}-{$seatSuffix}";
                              } else {
                                  $displaySeatLabel = "{$roomNum}-Block {$blockLetter}";
                              }
                            @endphp
                            <div
                              class="seat room-box {{ $isSeatBooked ? 'is-disabled' : '' }}"
                              data-room="{{ $seatUniqueName }}"
                              data-floor="{{ $floorName }}"
                              data-room-no="{{ $room->room_no }}"
                              data-price="{{ (int)$seat->price }}"
                              data-advance-price="{{ (int)$seat->advance_price }}"
                              data-status="{{ $isSeatBooked ? 1 : 0 }}"
                              data-room-type="{{ $room->room_type }}"
                              data-ac-status="{{ $room->ac_status }}"
                              title="">
                              {{ $displaySeatLabel }}
                              <div class="room-hover-info">
                                 @if($isSeatBooked)
                                  <div class="text-center fw-bold" style="color: #ea580c;">Already Booked</div>
                                  @php
                                    $uType = $bookedSeatsUserTypes[$seatUniqueName] ?? 'student';
                                    $uTypeLabel = ($uType === 'passenger' || $uType === 'Working Professional') ? 'Working Professional (চাকরিজীবী/পেশাজীবী)' : 'Student (শিক্ষার্থী)';
                                  @endphp
                                  <div class="text-center small mt-1 fw-bold text-dark" style="border-top: 1px dashed #ddd; padding-top: 4px;">Occupant: {{ $uTypeLabel }}</div>
                                @else
                                  @php
                                    $displayRoomType = $room->room_type;
                                    $rawType = trim($displayRoomType ?? '');
                                    if (in_array($rawType, ['Singel', 'Single'])) {
                                        $displayRoomType = 'Single Room';
                                    } elseif (in_array($rawType, ['Doubel', 'Double'])) {
                                        $displayRoomType = 'Double Room';
                                    }
                                  @endphp
                                  <div><strong>Room Type:</strong> {{ $displayRoomType }}</div>
                                  <div><strong>Monthly Rent:</strong> ৳{{ $seat->price }}</div>
                                  @if((int)$seat->advance_price > 0)
                                    <div><strong>Advance Deposit:</strong> ৳{{ $seat->advance_price }}</div>
                                  @endif
                                  @if($devFeeActive)
                                    <div><strong>Development Fee:</strong> ৳{{ number_format($devFeeAmount) }}</div>
                                  @endif
                                @endif
                              </div>
                            </div>
                          @endforeach
                        @endif
                      @endforeach
                    @endforeach
                  @elseif(isset($floors) && count($floors) > 0)
                    @foreach($floors as $floor)
                      <div class="w-100 mb-2 mt-3 floor-header-section" data-floor-name="{{ $floor->name }}">
                        <h6 class="fw-bold text-center mb-2" style="color: #033364; border-bottom: 1px dashed #bcd8f5; padding-bottom: 6px;">
                          <i class="bi bi-layers-half me-1"></i> {{ $floor->name }}
                        </h6>
                      </div>
                      @php
                        $seatsCollection = collect();
                        foreach($floor->rooms ?? collect() as $room) {
                            foreach($room->seats ?? collect() as $seat) {
                                $seatsCollection->push(['room' => $room, 'seat' => $seat]);
                            }
                        }
                        $groupedSections = [];
                        foreach($seatsCollection as $item) {
                            $rNo = (string)($item['room']->room_no ?? '');
                            if (preg_match('/^([A-Za-z]+)[\-_]/', $rNo, $m)) {
                                $sectionKey = 'Block ' . strtoupper($m[1]);
                            } elseif (preg_match('/(Block[\-_ ]?[A-Za-z])/i', $rNo, $m)) {
                                $sectionKey = ucwords(str_replace(['_', '-'], ' ', $m[1]));
                            } else {
                                $sectionKey = 'Block A';
                            }
                            $groupedSections[$sectionKey][] = $item;
                        }
                        uksort($groupedSections, function($a, $b) {
                            return strnatcasecmp($a, $b);
                        });
                        $seatSections = collect($groupedSections)->map(function($items) {
                            return collect($items)->sort(function($a, $b) {
                                $rNoA = (string)($a['room']->room_no ?? '');
                                $rNoB = (string)($b['room']->room_no ?? '');
                                
                                preg_match('/^([A-Za-z]+)[\-_](\d+)$/', $rNoA, $mA);
                                preg_match('/^([A-Za-z]+)[\-_](\d+)$/', $rNoB, $mB);
                                
                                $numA = isset($mA[2]) ? (int)$mA[2] : 0;
                                $numB = isset($mB[2]) ? (int)$mB[2] : 0;
                                
                                if ($numA !== $numB) {
                                    return $numA <=> $numB;
                                }
                                
                                $sA = (string)($a['seat']->seat_no ?? '');
                                $sB = (string)($b['seat']->seat_no ?? '');
                                return strnatcasecmp($sA, $sB);
                            });
                        });
                      @endphp
                      @foreach($seatSections as $sectionName => $secSeats)
                        @if($secSeats->count() > 0)
                          <div class="w-100 mb-2 mt-2">
                            <h6 class="fw-bold text-center text-secondary mb-1" style="font-size: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px;">{{ $sectionName }}</h6>
                          </div>
                          @foreach($secSeats as $item)
                            @php
                              $room = $item['room'];
                              $seat = $item['seat'];
                              $seatUniqueName = $room->room_no . '-' . $seat->seat_no;
                              $isSeatBooked = (isset($bookedRoomNumbers) && in_array($seatUniqueName, $bookedRoomNumbers)) || $seat->status == 1;
                              
                              $rNo = (string)($room->room_no ?? '');
                              $blockLetter = 'A';
                              $roomNum = 1;
                              if (preg_match('/^([A-Za-z]+)[\-_](\d+)$/', $rNo, $m)) {
                                  $blockLetter = strtoupper($m[1]);
                                  $roomNum = (int)$m[2];
                              }
                              
                              $seatCount = $room->seats->count();
                              if ($seatCount > 1) {
                                  $sNo = (string)($seat->seat_no ?? '');
                                  $seatSuffix = '1';
                                  if (preg_match('/(\d+)$/', $sNo, $sm)) {
                                      $seatSuffix = $sm[1];
                                  } elseif (str_contains(strtolower($sNo), '2') || str_contains(strtolower($sNo), 'b')) {
                                      $seatSuffix = '2';
                                  }
                                  $displaySeatLabel = "{$roomNum}-Block {$blockLetter}-{$seatSuffix}";
                              } else {
                                  $displaySeatLabel = "{$roomNum}-Block {$blockLetter}";
                              }
                            @endphp
                            <div
                              class="seat room-box {{ $isSeatBooked ? 'is-disabled' : '' }}"
                              data-room="{{ $seatUniqueName }}"
                              data-floor="{{ $floor->name }}"
                              data-room-no="{{ $room->room_no }}"
                              data-price="{{ (int)$seat->price }}"
                              data-advance-price="{{ (int)$seat->advance_price }}"
                              data-status="{{ $isSeatBooked ? 1 : 0 }}"
                              data-room-type="{{ $room->room_type }}"
                              data-ac-status="{{ $room->ac_status }}"
                              title="">
                              @if($seatCount > 1)
                                <span class="double-tag">Double</span>
                              @endif
                              {{ $displaySeatLabel }}
                              <div class="room-hover-info">
                                 @if($isSeatBooked)
                                  <div class="text-center fw-bold" style="color: #ea580c;">Already Booked</div>
                                  @php
                                    $uType = $bookedSeatsUserTypes[$seatUniqueName] ?? 'student';
                                    $uTypeLabel = ($uType === 'passenger' || $uType === 'Working Professional') ? 'Working Professional (চাকরিজীবী/পেশাজীবী)' : 'Student (শিক্ষার্থী)';
                                  @endphp
                                  <div class="text-center small mt-1 fw-bold text-dark" style="border-top: 1px dashed #ddd; padding-top: 4px;">Occupant: {{ $uTypeLabel }}</div>
                                @else
                                  @php
                                    $displayRoomType = $room->room_type;
                                    $rawType = trim($displayRoomType ?? '');
                                    if (in_array($rawType, ['Singel', 'Single'])) {
                                        $displayRoomType = 'Single Room';
                                    } elseif (in_array($rawType, ['Doubel', 'Double'])) {
                                        $displayRoomType = 'Double Room';
                                    }
                                  @endphp
                                  <div><strong>Room Type:</strong> {{ $displayRoomType }}</div>
                                  <div><strong>Monthly Rent:</strong> ৳{{ $seat->price }}</div>
                                  @if((int)$seat->advance_price > 0)
                                    <div><strong>Advance Deposit:</strong> ৳{{ $seat->advance_price }}</div>
                                  @endif
                                  @if($devFeeActive)
                                    <div><strong>Development Fee:</strong> ৳{{ number_format($devFeeAmount) }}</div>
                                  @endif
                                @endif
                              </div>
                            </div>
                          @endforeach
                        @endif
                      @endforeach
                    @endforeach
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky bar -->
        <div class="bk-sticky" id="stickyBar" style="display:none;">
          <div class="bk-sticky-left">
            <div class="bk-count"><span id="selectedCount">0</span> Room(s) selected</div>
            <div class="bk-sub" id="selectedListText"></div>
          </div>
          <div class="bk-sticky-right">
            <div class="bk-total"><span style="font-size:10px;font-weight:normal;opacity:0.8;display:block;text-align:right">Advance (অগ্রিম)</span>৳<span id="totalAmount">0</span></div>
            <button class="bk-btn" id="openBookingBtn" type="button" disabled>Booking</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===================== BOOKING FORM (HIDDEN) ===================== -->
  <div class="offcanvas-body booking-canvas-body d-none" id="openbooking">
    <!-- Message Box -->
    <div id="formMsg" class="alert d-none rounded-4 mb-3" role="alert"></div>

    <div class="bk-form-head">
      <div class="bk-form-title">
        <i class="bi bi-pencil-square"></i> Booking Form
        <small class="text-muted d-block">Selected rooms + guest info + payment</small>
      </div>
      <button type="button" class="bk-form-close" id="closeBookingBtn" aria-label="Close form">&times;</button>
    </div>
    
    <form id="bookingForm" method="POST" action="{{ route('room-booking-history.store') }}" enctype="multipart/form-data" novalidate>
      @csrf
      <!-- Selected rooms + total -->
      <div class="bk-block mb-3">
        <h6 class="bk-title"><i class="bi bi-grid-3x3-gap"></i> Selected Room(s)</h6>
        <div class="bk-summary">
          <div class="bk-s-row">
            <div class="bk-s-item">
              <span>Rooms:</span>
              <b id="finalRooms">—</b>
            </div>
            <div class="bk-s-item price">
              <span>Advance Deposit (অগ্রিম):</span>
              <b>৳<span id="finalTotal">0</span></b>
            </div>
          </div>
        </div>
        <input type="hidden" id="selectedSeatsInput" name="rooms" value="">
        <input type="hidden" id="selectedTotalInput" name="total" value="">
      </div>

      <!-- Guest Info -->
      <div class="bk-block mb-3">
        <h6 class="bk-title"><i class="bi bi-person-badge"></i> Guest Information</h6>
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">Phone <span class="req">*</span></label>
            <div class="input-group">
              <span class="input-group-text">+88</span>
              <input 
                type="tel" 
                class="form-control" 
                id="phone" 
                name="phone"
                placeholder=""
                autocomplete="off"
              >
            </div>
            <div id="phoneStatus" class="form-text"></div>
          </div>

          <div class="col-12">
            <label class="form-label">Image <span class="req">*</span></label>
            <input type="file" class="form-control" id="guestImage" name="image" accept="image/*" >
          </div>

          <div class="col-12">
            <label class="form-label">Image Show <span class="req">*</span></label>
            <div class="img-preview" id="imgPreviewBox">
              <img id="imgPreview" alt="Preview" style="display:none; max-width:100%; height:auto;">
              <div class="hint" id="imgHint">Upload an image to preview here</div>
            </div>
          </div>

          <!-- Admission Date -->
          <div class="col-12">
              <label class="form-label">Admission Date (ভর্তি তারিখ) <span class="req">*</span></label>
              <input type="text" id="bkAdmissionDisplay" class="form-control" placeholder="DD-MM-YYYY" readonly>
              <input type="hidden" id="bkCheckin" name="check_in" class="hiddendisplay">
              <input type="hidden" id="bkCheckout" name="check_out" class="hiddendisplay">
          </div>

          <!-- Other Guest Information -->
          <div class="col-12">
            <label class="form-label">Full Name <span class="req">*</span></label>
            <input type="text" class="form-control" id="fullname" name="full_name" placeholder="Your name" >
          </div>

          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="guestEmail" name="email" placeholder="you@email.com">
          </div>

          <div class="col-12">
            <label class="form-label">Blood Group (রক্তের গ্রুপ)</label>
            <select class="form-select" id="blood_group" name="blood_group">
              <option value="">Select Blood Group</option>
              <option value="A+">A+</option>
              <option value="A-">A-</option>
              <option value="B+">B+</option>
              <option value="B-">B-</option>
              <option value="O+">O+</option>
              <option value="O-">O-</option>
              <option value="AB+">AB+</option>
              <option value="AB-">AB-</option>
            </select>
          </div>

          <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0">Password <span class="req">*</span></label>
                <div id="resetPasswordOption" style="display:none;">
                  <div class="form-check form-check-inline m-0">
                    <input class="form-check-input" type="checkbox" id="is_reset_password" name="is_reset_password" value="1">
                    <label class="form-check-label text-danger fw-bold" for="is_reset_password" style="font-size:12px; cursor:pointer;">
                      🔑 Forgot / Set New Password
                    </label>
                  </div>
                </div>
              </div>
              <input type="text" class="form-control font-monospace" id="bookingPassword" name="password" placeholder="Max 6 digit password" autocomplete="off" maxlength="6" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);">
              <div class="form-text" id="passwordHint" style="color:#888; font-size:12px;">🔒 Maximum 6 digits (Only Numbers). <strong>এই password আপনার login password হবে।</strong></div>
          </div>

          <input type="hidden" id="user_type" name="user_type" value="student">

          <!-- Student Guardian & Education Details -->
          <div class="col-12 p-0 m-0" id="student_fields">
            <div class="row g-3 p-0 m-0">
              <div class="col-12">
                <label class="form-label">Institution Name (শিক্ষা প্রতিষ্ঠানের নাম) <span class="req">*</span></label>
                <input type="text" class="form-control" id="institution_name" name="institution_name" placeholder="Enter Institution Name" required>
              </div>

              <div class="col-6">
                <label class="form-label">Education System (শিক্ষা ব্যবস্থা) <span class="req">*</span></label>
                <select class="form-select" id="education_level" name="education_level" required>
                  <option value="">Select System</option>
                  <option value="School">School (স্কুল)</option>
                  <option value="College">College (কলেজ)</option>
                  <option value="University">University (বিশ্ববিদ্যালয়)</option>
                  <option value="Diploma / Polytechnic">Diploma / Polytechnic (ডিপ্লোমা)</option>
                </select>
              </div>

              <div class="col-6">
                <label class="form-label">Class / Semester <span class="req">*</span></label>
                <select class="form-select" id="education_class" name="education_class" required>
                  <option value="">Select Class / Semester</option>
                </select>
              </div>

              <div class="col-6">
                <label class="form-label">Father's Name <span class="req">*</span></label>
                <input type="text" class="form-control" id="father_name" name="father_name" placeholder="Father's Name" required>
              </div>

              <div class="col-6">
                <label class="form-label">Mother's Name <span class="req">*</span></label>
                <input type="text" class="form-control" id="mother_name" name="mother_name" placeholder="Mother's Name" required>
              </div>

              <div class="col-6">
                <label class="form-label">Father's NID</label>
                <input type="text" class="form-control" id="father_nid" name="father_nid" placeholder="Father's NID number">
              </div>

              <div class="col-6">
                <label class="form-label">Mother's NID</label>
                <input type="text" class="form-control" id="mother_nid" name="mother_nid" placeholder="Mother's NID number">
              </div>

              <div class="col-6">
                <label class="form-label">Father's Phone</label>
                <input type="text" class="form-control" id="father_phone" name="father_phone" placeholder="01XXXXXXXXX" maxlength="11">
              </div>

              <div class="col-6">
                <label class="form-label">Mother's Phone</label>
                <input type="text" class="form-control" id="mother_phone" name="mother_phone" placeholder="01XXXXXXXXX" maxlength="11">
              </div>
            </div>
          </div>
          <!-- Division -->
           <div class="col-12">
            <label class="form-label">Division</label>
            <select class="form-select" id="division" name="division_id">
                <option value="">Select Division</option>
            </select>
          </div>
          <!-- District Dropdown -->
          <div class="col-12">
            <label class="form-label">District</label>
            <select class="form-select" id="district" name="district_id">
                <option value="">Select District</option>
            </select>
          </div>
          <!-- Thana Dropdown -->
          <div class="col-12">
            <label class="form-label">Thana</label>
            <select class="form-select" id="thana" name="thana_id">
                <option value="">Select Thana</option>
            </select>
          </div>
          <!-- Permanent Address Field -->
          <div class="col-12 mt-2">
            <label class="form-label">Permanent Address / স্থায়ী ঠিকানা <code>*</code></label>
            <input type="text" class="form-control" id="address" name="address" placeholder="Enter permanent address (Village/Road, Post Office, House No)" required>
          </div>
        </div>
      </div>
      <!-- Payment Options -->
      <div class="bk-block mb-3">
        @if($devFeeActive)
        <div class="p-3 mb-3 rounded-4 border border-warning" id="devFeeBreakdown" style="display: none; background-color: #fffdf5;">
          <div class="d-flex justify-content-between align-items-center text-secondary mb-1 fs-6">
            <span>অ্যাডভান্স ডিপোজিট (Advance Deposit):</span>
            <span class="fw-bold">৳<span id="advanceTotalDisplay">0</span></span>
          </div>
          <div class="d-flex justify-content-between align-items-center text-warning-emphasis mb-2 fs-6 fw-bold">
            <span>উন্নয়ন ফি (Development Fee - এককালীন):</span>
            <span>+ ৳{{ number_format($devFeeAmount) }}</span>
          </div>
          <hr class="my-2" style="border-top: 1px dashed #d97706;">
          <div class="d-flex justify-content-between align-items-center text-dark fw-bold fs-6">
            <span>মোট পরিশোধযোগ্য (Total Amount):</span>
            <span class="text-success fs-5 fw-bold">৳<span id="grandTotalDisplay">0</span></span>
          </div>
        </div>
        @endif
        <div class="payment-head-row">
          <h6 class="bk-title"><i class="bi bi-credit-card"></i> Payment Option</h6>
          <div class="total-amountsss">
            <span class="amount-label">Total Amount</span>
            <div class="amouont">৳<span id="grandTotal">0</span></div>
          </div>
        </div>
        <div class="pay-grid">
          <label class="pay-option active" for="payOnline">
            <input type="radio" name="payment" id="payOnline" value="online" checked>
            <div>
              <div class="p-title">Pay Online</div>
              <div class="p-sub">bKash / Nagad / Card. Booking confirm after verify.</div>
            </div>
          </label>
          <label class="pay-option" for="payCash">
            <input type="radio" name="payment" id="payCash" value="cash">
            <div>
              <div class="p-title">Pay Cash In</div>
              <div class="p-sub">Pay at hotel reception during check-in.</div>
            </div>
          </label>
        </div>
        <div id="onlineFields" class="mt-3">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Payment Method <span class="req">*</span></label>
              <select class="form-select" id="payMethod" name="pay_method">
                <option value="bKash" selected>bKash</option>
                <option value="Nagad">Nagad</option>
                <option value="Card">Card</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Transaction ID <span class="req">*</span></label>
              <input type="text" class="form-control" id="trx" name="trx" placeholder="Enter Transaction ID">
            </div>
          </div>
          <div class="hint mt-2">We’ll verify your payment and confirm the booking.</div>
        </div>
        <div id="cashFields" class="mt-3" style="display:none;">
          <div class="alert alert-warning mb-0 rounded-4">
            <strong>Pay at hotel:</strong> Please arrive on time. Booking may be cancelled if no-show.
          </div>
        </div>
      </div>
      <div class="d-grid gap-2">
        <button class="btn btn-success rounded-4 py-2 fw-bold" type="submit" id="confirmBtn">
          Confirm Booking <i class="bi bi-check2-circle ms-1"></i>
        </button>
        <button class="btn btn-outline-secondary rounded-4 py-2 fw-bold" type="button" id="backToSeatsBtn">
          Back
        </button>
      </div>
    </form>
  </div>
</div>


<script>

    document.getElementById('phone').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length > 11) {
        this.value = this.value.slice(0, 11);
    }
});

window.openroombookingModal = function () {
    const canvasEl = document.getElementById('bookingCanvas');
    if (!canvasEl || typeof bootstrap === 'undefined') return;
    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(canvasEl);
    offcanvas.show();
};

document.addEventListener('DOMContentLoaded', function () {

  const $ = (s, p = document) => p.querySelector(s);
  const $$ = (s, p = document) => [...p.querySelectorAll(s)];

    const els = {
        openBookingCanvasBtn: $('#openBookingCanvasBtn'),
        seatWrap: $('#seatGridWrap'),
        stickyBar: $('#stickyBar'),
        selectedCount: $('#selectedCount'),
        selectedListText: $('#selectedListText'),
        totalAmount: $('#totalAmount'),
        finalRooms: $('#finalRooms'),
        finalTotal: $('#finalTotal'),
        selectedSeatsInput: $('#selectedSeatsInput'),
        selectedTotalInput: $('#selectedTotalInput'),
        formMsg: $('#formMsg'),
        guestImage: $('#guestImage'),
        imgPreview: $('#imgPreview'),
        imgHint: $('#imgHint'),
        bkAdmissionDisplay: $('#bkAdmissionDisplay'),
        bkCheckin: $('#bkCheckin'),
        bkCheckout: $('#bkCheckout'),
        payOnline: $('#payOnline'),
        payCash: $('#payCash'),
        onlineFields: $('#onlineFields'),
        cashFields: $('#cashFields'),
        trx: $('#trx'),
        payMethod: $('#payMethod'),
        closeBookingBtn: $('#closeBookingBtn'),
        openBookingBtn: $('#openBookingBtn'),
        openbooking: $('#openbooking'),
        backToSeatsBtn: $('#backToSeatsBtn'),
        bookingForm: $('#bookingForm'),
        confirmBtn: $('#confirmBtn'),
        bookingCanvas: $('#bookingCanvas'),
        paymentAmount: $('#payment_amount'),
        division: $('#division'),
        district: $('#district'),
        thana: $('#thana'),
        password: $('#password'),
        bookingLoader: $('#bookingLoader'),
    };

    let selected = new Map();
    let existingImageUrl = null;
    let existingImageName = null;
    let guestHasAccount = false;

    function showLoader() {
        if (els.bookingLoader) {
            els.bookingLoader.classList.remove('d-none');
        }
        document.body.classList.add('booking-loading');
    }

    function hideLoader() {
        if (els.bookingLoader) {
            els.bookingLoader.classList.add('d-none');
        }
        document.body.classList.remove('booking-loading');
    }

    function showMsg(type, html) {
        if (!els.formMsg) return;

        let bg = '#eef2ff', color = '#1e293b', border = '#cbd5e1';
        if (type === 'success') {
            bg = '#dcfce7'; color = '#166534'; border = '#86efac';
        } else if (type === 'warning') {
            bg = '#ffedd5'; color = '#9a3412'; border = '#fdba74';
        } else if (type === 'danger') {
            bg = '#fee2e2'; color = '#991b1b'; border = '#fca5a5';
        }

        els.formMsg.classList.remove('d-none');
        els.formMsg.style.background = bg;
        els.formMsg.style.color = color;
        els.formMsg.style.border = `1px solid ${border}`;
        els.formMsg.style.borderRadius = '14px';
        els.formMsg.style.padding = '14px 16px';
        els.formMsg.style.marginBottom = '14px';
        els.formMsg.style.boxShadow = '0 8px 20px rgba(0,0,0,0.08)';
        els.formMsg.innerHTML = html;
    }

    function hideMsg() {
        if (!els.formMsg) return;
        els.formMsg.classList.add('d-none');
        els.formMsg.innerHTML = '';
        els.formMsg.removeAttribute('style');
    }

    function resetBookingForm() {
        if (els.bookingForm) els.bookingForm.reset();

        if (els.imgPreview) {
            els.imgPreview.src = '';
            els.imgPreview.style.display = 'none';
        }
        if (els.imgHint) {
            els.imgHint.style.display = 'block';
        }

        const existingImgInput = document.getElementById('existingImageInput');
        if (existingImgInput) {
            existingImgInput.value = '';
        }

        if (els.guestImage) {
            els.guestImage.value = '';
            els.guestImage.setAttribute('required', 'required');
        }

        existingImageUrl = null;
        existingImageName = null;
        guestHasAccount = false;

        const resetOpt = document.getElementById('resetPasswordOption');
        if (resetOpt) resetOpt.style.display = 'none';
        const pwHint = document.getElementById('passwordHint');
        if (pwHint) pwHint.innerHTML = '';

        if (els.district) els.district.innerHTML = '<option value="">Select District</option>';
        if (els.thana) els.thana.innerHTML = '<option value="">Select Thana / Upazila</option>';
    }

function showSuccessAlert(message, redirectUrl) {
    if (typeof selected !== 'undefined') {
        selected.clear();
        if (typeof syncSticky === 'function') syncSticky();
    }

    if (els.openbooking) els.openbooking.classList.add('d-none');
    if (els.seatWrap) els.seatWrap.classList.remove('d-none');
    resetBookingForm();

    const offcanvasEl = document.getElementById('bookingCanvas');
    if (offcanvasEl && window.bootstrap) {
        const bsOffcanvas = window.bootstrap.Offcanvas.getInstance(offcanvasEl);
        if (bsOffcanvas) bsOffcanvas.hide();
    }

    const modalEl = document.getElementById('bookingPendingModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        setTimeout(() => {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }, 200);
    }
}
    window.openroombookingModal = function () {
        const canvasEl = document.getElementById('bookingCanvas');
        if (!canvasEl || typeof bootstrap === 'undefined') return;
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(canvasEl);
        offcanvas.show();
    };

    function bindOpenCanvas() {
        els.openBookingCanvasBtn?.addEventListener('click', function () {
            window.openroombookingModal();
        });
    }

    function getSelectedRoomPayload() {
        return [...selected.values()].map(item => ({
            floornumber: item.floornumber,
            roomnumber: item.roomnumber,
            price: Number(item.price),
            advance_price: Number(item.advance_price || 0)
        }));
    }

    function syncSticky() {
        const roomPayload = getSelectedRoomPayload();
        const rooms = roomPayload.map(item => item.roomnumber);
        const monthlyTotal = roomPayload.reduce((sum, item) => sum + Number(item.price || 0), 0);
        const advanceTotal = roomPayload.reduce((sum, item) => sum + Number(item.advance_price || 0), 0);

        const checkIn = new Date(els.bkCheckin?.value);
        const checkOut = new Date(els.bkCheckout?.value);

        const days = (els.bkCheckin?.value && els.bkCheckout?.value)
            ? Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24))
            : 1;

        const months = Math.max(1, Math.round(days / 30));
        const isDevFeeActive = {{ $devFeeActive ? 'true' : 'false' }};
        const devFeeAmount = {{ $devFeeAmount }};
        const currentDevFee = (rooms.length > 0 && isDevFeeActive) ? devFeeAmount : 0;
        const grandTotal = advanceTotal + currentDevFee;

        if (els.selectedCount) els.selectedCount.textContent = rooms.length;
        if (els.selectedListText) els.selectedListText.textContent = rooms.length ? rooms.join(', ') : '';
        if (els.totalAmount) els.totalAmount.textContent = grandTotal.toLocaleString();
        if (els.finalRooms) els.finalRooms.textContent = rooms.length ? rooms.join(', ') : '—';
        if (els.finalTotal) els.finalTotal.textContent = grandTotal.toLocaleString();
        if (els.selectedSeatsInput) els.selectedSeatsInput.value = JSON.stringify(roomPayload);
        if (els.selectedTotalInput) els.selectedTotalInput.value = grandTotal;
        if (els.paymentAmount) els.paymentAmount.value = grandTotal;
        if (els.stickyBar) els.stickyBar.style.display = rooms.length ? 'flex' : 'none';
        if (els.openBookingBtn) els.openBookingBtn.disabled = rooms.length === 0;

        const advanceEl = document.getElementById('advanceTotalDisplay');
        if (advanceEl) advanceEl.textContent = advanceTotal.toLocaleString();

        const grandEl = document.getElementById('grandTotalDisplay');
        if (grandEl) grandEl.textContent = grandTotal.toLocaleString();

        const devFeeBox = document.getElementById('devFeeBreakdown');
        if (devFeeBox) devFeeBox.style.display = rooms.length ? 'block' : 'none';

        const grandTotalEl = document.getElementById('grandTotal');
        if (grandTotalEl) grandTotalEl.textContent = grandTotal.toLocaleString();
    }

    function bindRooms() {
        $$('.room-box').forEach(el => {
            el.addEventListener('click', () => {
                if (el.classList.contains('is-disabled')) return;
                if (el.classList.contains('is-pending')) {
                    if (typeof Toastify === 'function') {
                        Toastify({
                            text: "⚠️ এই সিটটির একটি বুকিং আবেদন প্রসেসিংয়ে রয়েছে। এডমিন অনুমোদনের জন্য অপেক্ষা করুন।",
                            duration: 4000,
                            gravity: "top",
                            position: "center",
                            style: { background: "#d97706", color: "#fff", borderRadius: "10px", padding: "12px 18px" }
                        }).showToast();
                    }
                    return;
                }
                const room = el.dataset.room;
                const floor = el.dataset.floor || '';
                const price = parseFloat(el.dataset.price || '0');
                const advancePrice = parseFloat(el.dataset.advancePrice || '0');

                if (selected.has(room)) {
                    selected.delete(room);
                    el.classList.remove('is-selected');
                } else {
                    selected.set(room, {
                        floornumber: floor,
                        roomnumber: room,
                        price: price,
                        advance_price: advancePrice
                    });
                    el.classList.add('is-selected');
                }

                syncSticky();
            });
        });
    }

    function togglePaymentUI() {
        const isOnline = !!els.payOnline?.checked;

        if (els.onlineFields) els.onlineFields.style.display = isOnline ? 'block' : 'none';
        if (els.cashFields) els.cashFields.style.display = isOnline ? 'none' : 'block';

    
    }

    function bindPayment() {
        els.payOnline?.addEventListener('change', togglePaymentUI);
        els.payCash?.addEventListener('change', togglePaymentUI);
        togglePaymentUI();
    }

    function bindFormToggle() {
        els.openBookingBtn?.addEventListener('click', () => {
            hideMsg();
            els.seatWrap?.classList.add('d-none');
            els.openbooking?.classList.remove('d-none');
        });

        els.backToSeatsBtn?.addEventListener('click', () => {
            hideMsg();
            els.openbooking?.classList.add('d-none');
            els.seatWrap?.classList.remove('d-none');
        });

        els.closeBookingBtn?.addEventListener('click', () => {
            hideMsg();
            els.openbooking?.classList.add('d-none');
            els.seatWrap?.classList.remove('d-none');
        });
    }

    function bindImagePreview() {
        els.guestImage?.addEventListener('change', e => {
            const file = e.target.files?.[0];

            if (!file) {
                if (els.imgPreview) {
                    els.imgPreview.src = '';
                    els.imgPreview.style.display = 'none';
                }
                if (els.imgHint) els.imgHint.style.display = 'block';
                return;
            }

            const url = URL.createObjectURL(file);

            if (els.imgPreview) {
                els.imgPreview.src = url;
                els.imgPreview.style.display = 'block';
            }
            if (els.imgHint) els.imgHint.style.display = 'none';
        });
    }

    function toYMD(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function bindDatepickers() {
        if (!window.flatpickr) return;

        if (els.bkAdmissionDisplay) {
            flatpickr(els.bkAdmissionDisplay, {
                dateFormat: 'd-m-Y',
                minDate: 'today',
                onChange(dates) {
                    if (dates[0]) {
                        const ymd = toYMD(dates[0]);
                        if (els.bkCheckin) els.bkCheckin.value = ymd;
                        if (els.bkCheckout) els.bkCheckout.value = ymd;
                        syncSticky();
                    }
                }
            });
        }
    }

    const educationClassOptions = {
        'School': [
            'Play Group', 'Nursery', 'KG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 
            'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10'
        ],
        'College': [
            '1st Year (একাদশ)', '2nd Year (দ্বাদশ)'
        ],
        'University': [
            '1st Semester', '2nd Semester', '3rd Semester', '4th Semester', 
            '5th Semester', '6th Semester', '7th Semester', '8th Semester'
        ],
        'Diploma / Polytechnic': [
            '1st Semester', '2nd Semester', '3rd Semester', '4th Semester', 
            '5th Semester', '6th Semester', '7th Semester', '8th Semester'
        ]
    };

    function updateEducationClassOptions(selectedLevel, selectedClass = null) {
        const classSelect = document.getElementById('education_class');
        if (!classSelect) return;

        classSelect.innerHTML = '<option value="">Select Class / Semester</option>';
        if (!selectedLevel || !educationClassOptions[selectedLevel]) return;

        educationClassOptions[selectedLevel].forEach(cls => {
            const opt = document.createElement('option');
            opt.value = cls;
            opt.textContent = cls;
            if (selectedClass && selectedClass === cls) {
                opt.selected = true;
            }
            classSelect.appendChild(opt);
        });
    }

    function bindUserTypeToggle() {
        const studentFields = document.getElementById('student_fields');
        const eduLevelEl = document.getElementById('education_level');

        if (studentFields) {
            studentFields.style.display = 'block';
        }

        eduLevelEl?.addEventListener('change', function () {
            updateEducationClassOptions(this.value);
        });
    }

    async function loadDivisions(selectedDivisionId = null) {
        if (!els.division) return;

        const res = await fetch('/divisions');
        const data = await res.json();

        els.division.innerHTML = '<option value="">Select Division</option>';

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            els.division.appendChild(option);
        });

        if (selectedDivisionId) {
            els.division.value = String(selectedDivisionId);
        }
    }

    async function loadDistricts(divisionId, selectedDistrictId = null) {
        const districtEl = document.getElementById('district');
        const thanaEl = document.getElementById('thana');

        if (!districtEl) return;

        districtEl.innerHTML = '<option value="">Select District</option>';
        if (thanaEl) {
            thanaEl.innerHTML = '<option value="">Select Thana</option>';
        }

        if (!divisionId) return;

        const res = await fetch(`/divisions/${divisionId}/districts`);
        const data = await res.json();

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            districtEl.appendChild(option);
        });

        if (selectedDistrictId) {
            districtEl.value = String(selectedDistrictId);
        }
    }

    async function loadThanas(districtId, selectedThanaId = null) {
        const thanaEl = document.getElementById('thana');
        if (!thanaEl) return;

        thanaEl.innerHTML = '<option value="">Select Thana</option>';

        if (!districtId) return;

        const res = await fetch(`/districts/${districtId}/thanas`);
        const data = await res.json();

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            thanaEl.appendChild(option);
        });

        if (selectedThanaId) {
            thanaEl.value = String(selectedThanaId);
        }
    }

    async function bindLocationDropdowns() {
        await loadDivisions();

        els.division?.addEventListener('change', async function () {
            await loadDistricts(this.value);
        });

        els.district?.addEventListener('change', async function () {
            await loadThanas(this.value);
        });
    }

    function bindPhoneAutofill() {
        const phoneEl = document.getElementById('phone');
        const statusEl = document.getElementById('phoneStatus');
        if (!phoneEl) return;

        let timer;
        phoneEl.addEventListener('input', function () {
            clearTimeout(timer);
            const val = this.value.trim();

            if (statusEl) {
                statusEl.textContent = '';
                statusEl.className = 'form-text';
            }
            guestHasAccount = false;
            existingImageUrl = null;
            existingImageName = null;
            const fileInput = document.getElementById('guestImage');
            if (fileInput) fileInput.setAttribute('required', 'required');
            const hiddenImg = document.getElementById('existingImageInput');
            if (hiddenImg) hiddenImg.value = '';
            if (val.length < 11) return;
            if (statusEl) {
                statusEl.textContent = 'Searching...';
                statusEl.className = 'form-text text-muted';
            }
            timer = setTimeout(() => {
                fetch(`/guest/by-phone/${encodeURIComponent(val)}`)
                    .then(r => r.json())
                    .then(async data => {
                        const resetOpt = document.getElementById('resetPasswordOption');
                        const resetCb = document.getElementById('is_reset_password');

                        if (!data.found) {
                            guestHasAccount = false;
                            if (resetOpt) resetOpt.style.display = 'none';
                            if (resetCb) resetCb.checked = false;
                            if (statusEl) {
                                statusEl.textContent = '✅ New guest – fill in the form.';
                                statusEl.className = 'form-text text-success';
                            }
                            const pwHint = document.getElementById('passwordHint');
                            if (pwHint) { pwHint.innerHTML = '🔒 Maximum 6 digits. <strong>এই password আপনার login password হবে।</strong>'; pwHint.style.color = '#888'; }
                            return;
                        }

                        guestHasAccount = !!data.has_account;

                        if (data.has_account && resetOpt) {
                            resetOpt.style.display = 'block';
                        } else if (resetOpt) {
                            resetOpt.style.display = 'none';
                        }

                        if (statusEl) {
                            statusEl.textContent = data.has_account
                                ? '✅ Guest found! Account exists. Please enter password.'
                                : '✅ Guest found! Info auto-filled.';
                            statusEl.className = 'form-text text-success';
                        }

                        const pwHint = document.getElementById('passwordHint');
                        if (pwHint) { pwHint.innerHTML = '🔐 Guest found! Please enter your <strong>previously saved password</strong> to continue or check "Forgot / Set New Password".'; pwHint.style.color = '#1a7a4a'; }

                        const setVal = (id, value) => {
                            const el = document.getElementById(id);
                            if (el) el.value = value || '';
                        };

                        setVal('fullname', data.full_name);
                        setVal('guestEmail', data.email);
                        setVal('blood_group', data.blood_group);
                        setVal('nid', data.nid);
                        setVal('mother_nid', data.mother_nid);
                        setVal('father_nid', data.father_nid);
                        setVal('father_name', data.father_name);
                        setVal('mother_name', data.mother_name);
                        setVal('institution_name', data.institution_name);
                        setVal('workplace_name', data.workplace_name);
                        setVal('address', data.address);

                        if (data.user_type) {
                            setVal('user_type', data.user_type);
                            const userTypeEl = document.getElementById('user_type');
                            if (userTypeEl) userTypeEl.dispatchEvent(new Event('change'));
                        }

                        if (data.education_level) {
                            setVal('education_level', data.education_level);
                            updateEducationClassOptions(data.education_level, data.education_class);
                        }

                        if (data.image_url && data.image_name) {
                            existingImageUrl = data.image_url;
                            existingImageName = data.image_name;

                            const img = document.getElementById('imgPreview');
                            const hint = document.getElementById('imgHint');

                            if (img) {
                                img.src = data.image_url;
                                img.style.display = 'block';
                            }

                            if (hint) hint.style.display = 'none';
                            if (fileInput) fileInput.removeAttribute('required');

                            let hiddenExistingImg = document.getElementById('existingImageInput');
                            if (!hiddenExistingImg) {
                                hiddenExistingImg = document.createElement('input');
                                hiddenExistingImg.type = 'hidden';
                                hiddenExistingImg.id = 'existingImageInput';
                                hiddenExistingImg.name = 'existing_image';
                                document.getElementById('bookingForm').appendChild(hiddenExistingImg);
                            }
                            hiddenExistingImg.value = data.image_name;
                        }

                        if (data.division_id) {
                            await loadDivisions(data.division_id);
                            await loadDistricts(data.division_id, data.district_id);

                            if (data.district_id) {
                                await loadThanas(data.district_id, data.thana_id);
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (statusEl) {
                            statusEl.textContent = '⚠️ Error checking phone.';
                            statusEl.className = 'form-text text-danger';
                        }
                    });
            }, 500);
        });
    }


  function bindAjaxSubmit() {
      let isSubmitting = false;

      els.bookingForm?.addEventListener('submit', function (e) {
          e.preventDefault();
          if (isSubmitting) return;
          hideMsg();
          document.querySelectorAll('#bookingForm .form-control, #bookingForm .form-select').forEach(input => {
              input.style.cssText = '';
          });

      const passwordValue = document.querySelector('#bookingForm input[name="password"]')?.value.trim() || '';

        if (!passwordValue) {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🔑</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Password Required</div>
                        <div style="font-size:13px; line-height:1.6;">Please enter your password to complete the booking</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }
        if (!els.bkCheckin?.value || !els.bkCheckout?.value) {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">📅</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Admission Date Required</div>
                        <div style="font-size:13px; line-height:1.6;">Please select your Admission Date (ভর্তি তারিখ).</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }

        // STUDENT & FAMILY FIELDS CHECK
        const instName   = document.getElementById('institution_name')?.value.trim();
        const eduLevel   = document.getElementById('education_level')?.value;
        const eduClass   = document.getElementById('education_class')?.value;
        const fatherName = document.getElementById('father_name')?.value.trim();
        const motherName = document.getElementById('mother_name')?.value.trim();

        if (!instName || !eduLevel || !eduClass || !fatherName || !motherName) {
            const missing = [];
            if (!instName)   missing.push("Institution Name");
            if (!eduLevel)   missing.push("Education System");
            if (!eduClass)   missing.push("Class / Semester");
            if (!fatherName) missing.push("Father's Name");
            if (!motherName) missing.push("Mother's Name");
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🎓</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Student Information Required</div>
                        <div style="font-size:13px; line-height:1.6;">
                            Please fill in the following required field(s):<br>
                            <strong>${missing.join(', ')}</strong>
                        </div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }

        if (els.guestImage && !els.guestImage.files.length && !document.getElementById('existingImageInput')?.value) {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🖼️</div>
                    <div>
                      <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Guest Image Required</div>
                      <div style="font-size:13px; line-height:1.6;">Please upload a valid guest image to proceed.</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }
        if (!els.selectedSeatsInput?.value || els.selectedSeatsInput.value === '[]') {
            showMsg('warning', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🛏️</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">No room selected</div>
                        <div style="font-size:13px; line-height:1.6;">Please select at least one room.</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
            return;
        }
        if (els.payOnline?.checked) {
            if (!els.payMethod?.value.trim() || !els.trx?.value.trim()) {
                showMsg('warning', `
                    <div style="display:flex; gap:12px; align-items:flex-start;">
                        <div style="font-size:22px; line-height:1;">💳</div>
                        <div>
                            <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Payment information missing</div>
                            <div style="font-size:13px; line-height:1.6;">Please select payment method and enter transaction ID.</div>
                        </div>
                    </div>
                `);
                scrollToFormMsg();
                return;
            }
        }
        isSubmitting = true;
        showLoader();
        if (els.confirmBtn) {
            els.confirmBtn.disabled = true;
            els.confirmBtn.innerHTML = 'Processing...';
        }

        const formData = new FormData(els.bookingForm);

        fetch(els.bookingForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            let data = {};
            try {
                data = await res.json();
            } catch (e) {
                data = { status: false, message: 'Invalid server response' };
            }
            return { ok: res.ok, data };
        })
        .then(({ ok, data }) => {
            isSubmitting = false;
            hideLoader();

            if (els.confirmBtn) {
                els.confirmBtn.disabled = false;
                els.confirmBtn.innerHTML = 'Confirm Booking <i class="bi bi-check2-circle ms-1"></i>';
            }

            if (ok && data.status) {
                showSuccessAlert(data.message || 'Booking successfully!', data.redirect_url);
                return;
            }

            if (data.wrong_password || data.account_exists_alert) {
                const resetOpt = document.getElementById('resetPasswordOption');
                if (resetOpt) resetOpt.style.display = 'block';
                const pwHint = document.getElementById('passwordHint');
                if (pwHint) {
                    pwHint.innerHTML = '⚠️ Password did not match. Check <strong>"Forgot / Set New Password"</strong> above to enter a new password.';
                    pwHint.style.color = '#dc2626';
                }
            }

            showMsg('danger', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">⚠️</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Booking failed</div>
                        <div style="font-size:13px; line-height:1.6;">${data.message || 'Please try again.'}</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
        })
        .catch(error => {
            console.error(error);
            isSubmitting = false;
            hideLoader();

            if (els.confirmBtn) {
                els.confirmBtn.disabled = false;
                els.confirmBtn.innerHTML = 'Confirm Booking <i class="bi bi-check2-circle ms-1"></i>';
            }

            showMsg('danger', `
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <div style="font-size:22px; line-height:1;">🌐</div>
                    <div>
                        <div style="font-size:15px; font-weight:700; margin-bottom:6px;">Network error</div>
                        <div style="font-size:13px; line-height:1.6;">Please try again.</div>
                    </div>
                </div>
            `);
            scrollToFormMsg();
        });
    });
}

function scrollToFormMsg() {
    setTimeout(() => {
        els.formMsg?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

function resetBookingForm() {
    if (els.bookingForm) {
        els.bookingForm.reset();
    }

    if (els.phone) els.phone.value = '';

    if (els.guestImage) els.guestImage.value = '';
    const existingImgInput = document.getElementById('existingImageInput');
    if (existingImgInput) existingImgInput.value = '';

    if (els.imgPreview) {
        els.imgPreview.src = '';
        els.imgPreview.style.display = 'none';
    }
    if (els.imgHint) els.imgHint.style.display = 'block';

    selectedSeats = [];
    if (els.selectedSeatsInput) els.selectedSeatsInput.value = '[]';

    if (els.selectedSeatsList) els.selectedSeatsList.innerHTML = '<span class="text-muted small">No room selected</span>';
    if (els.selectedSeatsCount) els.selectedSeatsCount.textContent = '0';
    if (els.totalAdvancePrice) els.totalAdvancePrice.textContent = '৳ 0';

    document.querySelectorAll('.seat-checkbox, .room-seat-select').forEach(cb => {
        cb.checked = false;
        cb.closest('.seat-card, .room-card')?.classList.remove('selected', 'active');
    });

    const pwInput = document.getElementById('bookingPassword');
    if (pwInput) pwInput.value = '';
    const pwHint = document.getElementById('passwordHint');
    if (pwHint) {
        pwHint.innerHTML = '';
        pwHint.style.color = '';
    }
    const resetOpt = document.getElementById('resetPasswordOption');
    if (resetOpt) resetOpt.style.display = 'none';

    if (els.payCash) els.payCash.checked = true;
    if (els.payOnline) els.payOnline.checked = false;
    if (typeof togglePaymentUI === 'function') togglePaymentUI();

    if (els.newGuestCheck) els.newGuestCheck.checked = true;
    if (typeof toggleGuestForm === 'function') toggleGuestForm(true);

    if (els.openbooking) els.openbooking.classList.add('d-none');
    if (els.seatWrap) els.seatWrap.classList.remove('d-none');

    hideMsg();
}

document.getElementById('phone')?.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length > 11) {
        this.value = this.value.slice(0, 11);
    }
});

const bookingPwInputEl = document.getElementById('bookingPassword');
if (bookingPwInputEl) {
    bookingPwInputEl.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 6) {
            this.value = this.value.slice(0, 6);
        }
    });
    bookingPwInputEl.addEventListener('keydown', function (e) {
        // Allow backspace, delete, tab, escape, enter, arrows, and Ctrl/Cmd combos (copy/paste/select)
        if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 86 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        // If not a number key (top row 48-57 or numpad 96-105), prevent typing
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
}

function bindResetPasswordToggle() {
    const resetCb = document.getElementById('is_reset_password');
    const pwInput = document.getElementById('bookingPassword');
    const pwHint = document.getElementById('passwordHint');

    if (!resetCb) return;

    resetCb.addEventListener('change', function () {
        if (this.checked) {
            if (pwHint) {
                pwHint.innerHTML = '🔑 <strong>Set New Password mode active!</strong> Type a new 6-digit password. Your account password will be updated.';
                pwHint.style.color = '#d97706';
            }
            if (pwInput) pwInput.placeholder = 'Enter new 6 digit password';
        } else {
            if (pwHint) {
                pwHint.innerHTML = '🔐 Guest found! Please enter your <strong>previously saved password</strong> to continue.';
                pwHint.style.color = '#1a7a4a';
            }
            if (pwInput) pwInput.placeholder = 'Max 6 digit password';
        }
    });
}

function bindOffcanvasFilter() {
    const bookingCanvasEl = document.getElementById('bookingCanvas');
    if (!bookingCanvasEl) return;

    bookingCanvasEl.addEventListener('show.bs.offcanvas', function (e) {
        if (!selectedSeats || selectedSeats.length === 0) {
            if (els.openbooking) els.openbooking.classList.add('d-none');
            if (els.seatWrap) els.seatWrap.classList.remove('d-none');
        }

        const triggerEl = e.relatedTarget;
        if (triggerEl && triggerEl.classList.contains('bookNowBtn')) {
            const targetRoomNo = triggerEl.getAttribute('data-room');
            document.querySelectorAll('#seatGridWrap .room-box').forEach(el => {
                const elRoomNo = el.getAttribute('data-room-no');
                el.style.setProperty('display', elRoomNo === targetRoomNo ? 'flex' : 'none', 'important');
            });
            const firstMatchingSeat = document.querySelector(`#seatGridWrap .room-box[data-room-no="${targetRoomNo}"]`);
            const targetFloorName = firstMatchingSeat ? firstMatchingSeat.getAttribute('data-floor') : '';
            document.querySelectorAll('#seatGridWrap .floor-header-section').forEach(header => {
                const headerFloorName = header.getAttribute('data-floor-name');
                header.style.setProperty('display', headerFloorName === targetFloorName ? 'block' : 'none', 'important');
            });
        } else {
            document.querySelectorAll('#seatGridWrap .room-box').forEach(el => el.style.display = '');
            document.querySelectorAll('#seatGridWrap .floor-header-section').forEach(header => header.style.display = '');
            const resetOpt = document.getElementById('resetPasswordOption');
            if (resetOpt) resetOpt.style.display = 'none';
        }
    });
}

function checkAndShowSuccessModal() {
    if (sessionStorage.getItem('showBookingSuccessModal') === 'true') {
        sessionStorage.removeItem('showBookingSuccessModal');
        const modalEl = document.getElementById('bookingPendingModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            setTimeout(() => {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
            }, 200);
        }
    }
}

    function fetchLiveBookedSeats() {
        fetch('/get-live-booked-seats')
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    const approvedSet = new Set(data.approved_seats || data.booked_seats || []);
                    const pendingSet  = new Set(data.pending_seats || []);

                    document.querySelectorAll('#seatGridWrap .room-box').forEach(seatBox => {
                        const roomNo = seatBox.dataset.room;

                        if (approvedSet.has(roomNo)) {
                            // APPROVED -> RED (Disabled / Already Booked)
                            seatBox.classList.remove('is-pending', 'is-selected');
                            seatBox.classList.add('is-disabled');
                            seatBox.dataset.status = "1";
                            let hoverInfo = seatBox.querySelector('.room-hover-info');
                            if (!hoverInfo) {
                                hoverInfo = document.createElement('div');
                                hoverInfo.className = 'room-hover-info';
                                seatBox.appendChild(hoverInfo);
                            }
                            hoverInfo.innerHTML = `
                                <div class="text-center fw-bold" style="color: #ea580c;">Already Booked</div>
                                <div class="text-center small mt-1 fw-bold text-dark" style="border-top: 1px dashed #ddd; padding-top: 4px;">Occupant: Booked</div>
                            `;
                        } else if (pendingSet.has(roomNo)) {
                            // PENDING -> SOFT ORANGE/YELLOW (Booking Processing)
                            seatBox.classList.remove('is-disabled', 'is-selected');
                            seatBox.classList.add('is-pending');
                            seatBox.dataset.status = "0";
                            let hoverInfo = seatBox.querySelector('.room-hover-info');
                            if (!hoverInfo) {
                                hoverInfo = document.createElement('div');
                                hoverInfo.className = 'room-hover-info';
                                seatBox.appendChild(hoverInfo);
                            }
                            hoverInfo.innerHTML = `
                                <div class="text-center fw-bold" style="color: #d97706;">⏳ বুকিং প্রসেসিং চলছে</div>
                                <div class="text-center small mt-1 fw-bold text-dark" style="border-top: 1px dashed #ddd; padding-top: 4px;">Pending Approval</div>
                            `;
                        } else {
                            // VACANT -> Normal State
                            if (seatBox.classList.contains('is-disabled') || seatBox.classList.contains('is-pending')) {
                                seatBox.classList.remove('is-disabled', 'is-pending');
                                seatBox.dataset.status = "0";
                            }
                        }
                    });
                }
            })
            .catch(err => {});
    }

(async function init() {
    bindOpenCanvas();
    await bindLocationDropdowns();
    bindRooms();
    bindFormToggle();
    bindImagePreview();
    bindPayment();
    bindDatepickers();
    bindUserTypeToggle();
    bindOffcanvasFilter();
    bindAjaxSubmit();
    bindResetPasswordToggle();
    syncSticky();
    bindPhoneAutofill();
    checkAndShowSuccessModal();
    fetchLiveBookedSeats();
    setInterval(fetchLiveBookedSeats, 2500);
})();
});
</script>

<!-- Booking Pending Activation Success Modal -->
<div class="modal fade notranslate" id="bookingPendingModal" tabindex="-1" aria-hidden="true" style="z-index: 99999;" translate="no" lang="bn">
  <div class="modal-dialog modal-dialog-centered notranslate" style="max-width: 480px;" translate="no">
    <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden notranslate" translate="no">
      <div class="modal-header bg-success text-white py-3 px-4">
        <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2 mb-0" style="font-size: 1.15rem;">
          <i class="bi bi-check-circle-fill fs-4"></i>
          <span>বুকিং আবেদন সফলভাবে জমা হয়েছে!</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 text-center">
        @php
          $logoSetting = \App\Services\SettingService::getSettingContentBySlug('logo_setting');
          $modalLogo   = isset($logoSetting['logo']) ? asset($logoSetting['logo']) : asset('logo/logoimage (2).png');
        @endphp
        <div class="mb-3">
          <div class="d-inline-flex align-items-center justify-content-center rounded-circle p-2 shadow-sm" style="width: 85px; height: 85px; background: #ffffff; border: 3px solid #033364;">
            <img src="{{ $modalLogo }}" alt="TSS Villa Logo" style="width: 100%; height: 100%; object-fit: contain;">
          </div>
        </div>

        <h4 class="fw-extrabold mb-3" style="color: #033364 !important; font-size: 1.25rem; font-weight: 800;">টি এস এস ভিলায় (TSS Villa) আপনাকে স্বাগতম!</h4>
        
        <div class="card border-0 bg-light p-3 my-3 text-start rounded-3 border-start border-4 border-success">
          <p class="mb-2 text-dark small fw-semibold">
            <i class="bi bi-info-circle text-success me-1"></i>
            আপনার বুকিং আবেদন এবং পেমেন্ট সংক্রান্ত তথ্য সফলভাবে সিস্টেমে জমা হয়েছে।
          </p>
          <p class="mb-0 text-muted small">
            অ্যাডমিন আপনার বুকিং তথ্য যাচাই করে কনফার্ম ও একটিভ (Active) করার পর আপনি আপনার <strong>ফোন নম্বর ও পাসওয়ার্ড</strong> ব্যবহার করে ড্যাশবোর্ডে লগইন করতে পারবেন।
          </p>
        </div>

        <div class="p-2 text-primary-emphasis rounded-3 small fw-semibold text-start mb-2" style="background-color: #eff6ff; border: 1px solid #bfdbfe;">
          <i class="bi bi-telephone-outbound me-1 text-primary"></i>
          অ্যাডমিনের সাথে যোগাযোগ করুন এবং আপনার বুকিং রসিদ বুঝে নিন।
        </div>

        <div class="p-2 text-warning-emphasis rounded-3 small fw-semibold text-start mb-2" style="background-color: #fff9e6; border: 1px solid #fef08a;">
          <i class="bi bi-shield-lock me-1"></i>
          অ্যাডমিন একটিভ (Active) করার পরই ড্যাশবোর্ডে লগইন সম্ভব হবে।
        </div>
      </div>

      <div class="modal-footer bg-light py-3 px-4 justify-content-center">
        <button type="button" class="btn btn-secondary fw-bold px-4 rounded-3" data-bs-dismiss="modal">
          ✕ বন্ধ করুন
        </button>
      </div>
    </div>
  </div>
</div>