@extends('Frontend.layouts.app')
@section('title', 'Booking Now')
@section('content')

<section class="hero-search-wrap bookingpage" style="background-image: linear-gradient(rgba(3, 51, 100, 0.45), rgba(3, 51, 100, 0.45)), url('{{ asset('slider/373ae656.avif') }}');">
  <div class="container">
  </div>
</section>

<section class="stepbar-wrap">
  <div class="stepbar">
    <span class="stepbar-item is-active">Select Type of Room</span>
    <form id="filterForm" method="GET" class="d-flex align-items-center gap-3 ms-3">
      <label class="d-flex align-items-center gap-2 mb-0">
        <input type="checkbox" name="ac" value="1"
               onchange="document.getElementById('filterForm').submit()"
               {{ request()->has('ac') ? 'checked' : '' }}>
        <span>Ac</span>
      </label>
      <label class="d-flex align-items-center gap-2 mb-0">
        <input type="checkbox" name="nonac" value="1"
               onchange="document.getElementById('filterForm').submit()"
               {{ request()->has('nonac') ? 'checked' : '' }}>
        <span>Non Ac</span>
      </label>
    </form>
    @if(request()->has('ac') || request()->has('nonac'))
      <a href="{{ url()->current() }}" class="ms-3 small text-decoration-underline"></a>
    @endif
  </div>
</section>


<section class="ticket-wrap">
  <div class="container">
    <div class="row ticket-grid">
      @foreach($rooms as $room)
        <div class="col-6">
        <div class="ticket-card">
          <div class="ticket-body">
            <div class="t-side-left">
              <div class="t-img">
                  @php
                    $images = is_array($room->image) ? $room->image : json_decode($room->image, true);
                    $images = is_array($images) ? $images : [];
                  @endphp
                  @if(count($images) > 1)
                    <div id="slider_{{ $room->id }}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                      <div class="carousel-inner">
                        @foreach($images as $i => $img)
                          <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            <img src="{{ asset('room_image/' . $img) }}" alt="Room Image" class="room-slide-img" />
                          </div>
                        @endforeach
                      </div>
                      <button class="carousel-control-prev" type="button" data-bs-target="#slider_{{ $room->id }}" data-bs-slide="prev">
                      </button>
                      <button class="carousel-control-next" type="button" data-bs-target="#slider_{{ $room->id }}" data-bs-slide="next">
                      </button>
                    </div>
                  @elseif(count($images) === 1)
                    <img src="{{ asset('room_image/' . $images[0]) }}" alt="Room Image" class="room-slide-img" />
                  @else
                    <img src="{{ asset('images/no-image.jpg') }}" alt="No Image" class="room-slide-img" />
                  @endif
                </div>
            </div>
             <div class="t-side-right">
              <div class="t-box">
                <div class="t-new inportent">Floor : <span class="spanstatus">{{ $room->floor->name }}</span></div>
                <div class="t-new ">Room No : <span class="spanstatus ">{{ $room->room_no }}</span></div>
              </div>
              <div class="t-box">
                <div class="t-new">Room Type : <span class="spanstatus">{{ $room->room_type }}</span></div>
                <div class="t-new">Room Size : <span class="spanstatus">{{ $room->room_size}}</span></div>
                
              </div>
              <div class="t-left-info">
                <div class="t-new">Attached Bathroom : <span class="spanstatus">{{ $room->attached_bathroom }}</span></div>
                <div class="t-new">Balcony : <span class="spanstatus">{{ $room->balcony }}</span></div>
              </div>
              <div class="t-left-info">
                  <div class="t-new">Windows : <span class="spanstatus">{{ $room->windows }}</span></div>
                  <div class="t-new">Total Seats : <span class="spanstatus">{{ $room->seats->count() }}</span></div>
              </div>
              <div class="t-left-info">
                
                <div class="t-new ">Breakfast : <span class="spanstatus">{{ $room->breakfast }}</span></div>

              </div>
                <div class="t-left-info">
                <div class="t-new inportent">Price : <span class="spanstatus">{{ $room->price }}</span></div>
              </div>
              <div class="t-actions">
                <button
                class="t-btn bookNowBtn"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#bookingCanvas"
                    aria-controls="bookingCanvas"
                    data-room="{{ $room->room_no }}"
                    data-price="{{ (int)$room->price }}"
                  >
                BOOK NOW
              </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach

    </div>
  </div>
</section>


@endsection


<style>
.t-img {
  position: relative;
  overflow: hidden;
  cursor: crosshair;
  width: 100%;
}

.room-slide-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Carousel height fix */
.t-img .carousel,
.t-img .carousel-inner,
.t-img .carousel-item {
  height: 100%;
}

.carousel-control-prev,
.carousel-control-next {
  width: 28px;
  z-index: 20;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
  width: 18px;
  height: 18px;
}

/* Small lens on image */
.zoom-lens {
  position: absolute;
  width: 80px;
  height: 80px;
  border: 2px solid rgba(255, 255, 255, 0.95);
  border-radius: 50%;
  pointer-events: none;
  display: none;
  z-index: 15;
  background-repeat: no-repeat;
  box-shadow: 0 0 0 1px rgba(0,0,0,0.18), 0 3px 10px rgba(0,0,0,0.35);
}

/*  Clean Zoom Box (NO background / NO border / NO shadow) */
#global-zoom-box {
  position: fixed;
  width: 300px;
  height: 300px;
  background-repeat: no-repeat;
  display: none;
  z-index: 999999;
  pointer-events: none;

  /* removed box style */
  border: none;
  border-radius: 0;
  background-color: transparent;
  box-shadow: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const BOX_SIZE = 300;
  const ZOOM_RATIO = 3;
  const GAP = 25;
  let zoomBox = document.getElementById('global-zoom-box');
  if (!zoomBox) {
    zoomBox = document.createElement('div');
    zoomBox.id = 'global-zoom-box';
    document.body.appendChild(zoomBox);
  }
  document.querySelectorAll('.t-img').forEach(function (card) {
    let lens = card.querySelector('.zoom-lens');
    if (!lens) {
      lens = document.createElement('div');
      lens.className = 'zoom-lens';
      card.appendChild(lens);
    }
    let activeImg = null;
    function getActiveImg() {
      return card.querySelector('.carousel-item.active img') || card.querySelector('img');
    }
    function hideZoom() {
      lens.style.display = 'none';
      zoomBox.style.display = 'none';
      activeImg = null;
    }
    function showZoom() {
      activeImg = getActiveImg();
      if (!activeImg) return;
      lens.style.display = 'block';
      zoomBox.style.display = 'block';
    }
    card.addEventListener('mouseenter', showZoom);
    card.addEventListener('mousemove', function (e) {
      activeImg = getActiveImg();

      if (!activeImg) return;

      const imgRect = activeImg.getBoundingClientRect();
      const cardRect = card.getBoundingClientRect();

      if (
        e.clientX < imgRect.left ||
        e.clientX > imgRect.right ||
        e.clientY < imgRect.top ||
        e.clientY > imgRect.bottom
      ) {
        hideZoom();
        return;
      }

      lens.style.display = 'block';
      zoomBox.style.display = 'block';

      const mouseX = e.clientX - imgRect.left;
      const mouseY = e.clientY - imgRect.top;

      let lensX = e.clientX - cardRect.left - (lens.offsetWidth / 2);
      let lensY = e.clientY - cardRect.top - (lens.offsetHeight / 2);
      lensX = Math.max(0, Math.min(lensX, card.offsetWidth - lens.offsetWidth));
      lensY = Math.max(0, Math.min(lensY, card.offsetHeight - lens.offsetHeight));
      lens.style.left = lensX + 'px';
      lens.style.top = lensY + 'px';
      const bgWidth = imgRect.width * ZOOM_RATIO;
      const bgHeight = imgRect.height * ZOOM_RATIO;
      const bgX = -((mouseX * ZOOM_RATIO) - (BOX_SIZE / 2));
      const bgY = -((mouseY * ZOOM_RATIO) - (BOX_SIZE / 2));
      zoomBox.style.backgroundImage = "url('" + activeImg.src + "')";
      zoomBox.style.backgroundSize = bgWidth + 'px ' + bgHeight + 'px';
      zoomBox.style.backgroundPosition = bgX + 'px ' + bgY + 'px';
      lens.style.backgroundImage = "url('" + activeImg.src + "')";
      lens.style.backgroundSize = bgWidth + 'px ' + bgHeight + 'px';
      lens.style.backgroundPosition =
        -((mouseX * ZOOM_RATIO) - (lens.offsetWidth / 2)) + 'px ' +
        -((mouseY * ZOOM_RATIO) - (lens.offsetHeight / 2)) + 'px';
      let boxLeft = e.clientX + GAP;
      let boxTop = e.clientY + GAP;
      if (boxLeft + BOX_SIZE > window.innerWidth) {
        boxLeft = e.clientX - BOX_SIZE - GAP;
      }
      if (boxTop + BOX_SIZE > window.innerHeight) {
        boxTop = e.clientY - BOX_SIZE - GAP;
      }
      if (boxLeft < 10) boxLeft = 10;
      if (boxTop < 10) boxTop = 10;
      zoomBox.style.left = boxLeft + 'px';
      zoomBox.style.top = boxTop + 'px';
    });
    card.addEventListener('mouseleave', hideZoom);
    card.addEventListener('slid.bs.carousel', function () {
      activeImg = getActiveImg();
    });
  });
});
</script>




