@extends('frontend.layouts.app')

@section('content')
<style>
.pricing-header {
      padding: 3rem 1.5rem;
      text-align: center;
    }
    .card {
      margin: 1rem;
    }
    .selected-card {
      border: 2px solid #0d6efd;
    }
    .selected-card-header {
      background-color: #0d6efd;
      color: white;
    }
	.form-check-input {
      display: none;
    }
    .form-check-label {
      cursor: pointer;
      display: block;
      padding: 0.5rem;
      border: 1px solid #0d6efd;
      border-radius: 0.25rem;
      background-color: #f8f9fa;
      color: #0d6efd;
      transition: background-color 0.2s, color 0.2s;
    }
    .form-check-input:checked + .form-check-label {
      background-color: #0d6efd;
      color: white;
    }
</style>
<section class="pt-4 pb-4">
    <div class="container">
        <div class="d-flex justify-content-between">
            <h1 class="fw-600 h4">{{ translate('Register your shop')}}</h1>
            <ul class="breadcrumb bg-transparent p-0">
                <li class="breadcrumb-item opacity-50">
                    <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                </li>
                <li class="text-dark fw-600 breadcrumb-item">
                    <a class="text-reset" href="{{ route('shops.create') }}">"{{ translate('Register your shop')}}"</a>
                </li>
            </ul>
        </div>
    </div>
</section>
<section class="pt-4 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-xxl-12 col-xl-12 col-md-12 mx-auto">
                <form id="shop" class="" action="{{ route('shops.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
					<div id="step1">
						<h3>Select Package</h3>
						<div class="row row-cols-1 row-cols-md-3 mb-3 text-center">
						  <div class="col-md-4" >
							<div class="card mb-4 rounded-3 shadow-sm" id="cardA">
							  <div class="card-header text-center py-3">
								<h1 class="my-0 fw-normal">Plan A</h1>
							  </div>
							  <div class="card-body">
								<p>Vendor -55%<br>PastCart -45%<br>* Vendor doesnot have the GST number.The GST will be filed by the PastCart</p>
								<h5>Highlights</h5>
								<ul class="list-unstyled mt-3 mb-4">
								  <li>1. Applicable only in case of old comics and magazine.</li>
								  <li>2. Products will be uploaded by the PastCart team.</li>
								  <li>3. Upon sell, the product will be delivered by the PastCart team.</li>
								  <li>4. products will be hold by the PastCart. </li>
								  <li>5. The pricing of the product will be decided by the PastCart team.</li>
								</ul>
								<div class="form-check">
								  <input class="form-check-input" type="radio" name="commission" id="commissionA" value="45">
								  <label class="form-check-label" for="commissionA">
									Select Plan
								  </label>
								</div>
							  </div>
							</div>
						  </div>
						  <div class="col-md-4" >
							<div class="card mb-4 rounded-3 shadow-sm" id="cardB">
							  <div class="card-header py-3">
								<h1 class="my-0 fw-normal">Plan B</h1>
							  </div>
							  <div class="card-body">
								<p>Vendor -83%<br>
PastCart -17%<br>
* Vendors  have their own GST number.The GST will be filed by the concerned vendor</p>
<h5>Highlights</h5>
								<ul class="list-unstyled mt-3 mb-4">
								  <li>1. Includes all Items</li>
								  <li>2. Products will be uploaded by the PastCart team.</li>
								  <li>3. Products to be delivered by the vendors.</li>
								  <li>4.  Except uploading,No interference form the PastCart team.</li>
								</ul>
								<div class="form-check">
								  <input class="form-check-input" type="radio" name="commission" id="commissionB" value="17">
								  <label class="form-check-label" for="commissionB">
									Select Plan
								  </label>
								</div>
							  </div>
							</div>
						  </div>
						  <div class="col-md-4" >
							<div class="card mb-4 rounded-3 shadow-sm" id="cardC">
							  <div class="card-header py-3">
								<h1 class="my-0 fw-normal">Plan C</h1>
							  </div>
							  <div class="card-body">
								<p>Vendor -65%<br>
PastCart -35%<br>
* Vendor doesnot have the GST number.The GST will be filed by the PastCart</p>
<h5>Highlights</h5>
								<ul class="list-unstyled mt-3 mb-4">
								  <li>1. Includes all Items</li>
								  <li>2. Products will be uploaded by the PastCart team.</li>
								  <li>3. Upon sell, the product will be delivered by the vendors.</li>
								</ul>
								<div class="form-check">
								  <input class="form-check-input" type="radio" name="commission" id="commissionC" value="35">
								  <label class="form-check-label" for="commissionC">
									Select Plan
								  </label>
								</div>
							  </div>
							</div>
						  </div>
						  <div class="col-md-4" >
							<div class="card mb-4 rounded-3 shadow-sm" id="cardD">
							  <div class="card-header py-3">
								<h1 class="my-0 fw-normal">Plan D</h1>
							  </div>
							  <div class="card-body">
								<p>Vendor -90%<br>
PastCart -10%</p>
<h5>Highlights</h5>
								<ul class="list-unstyled mt-3 mb-4">
								  <li>1. Includes only old books, comics and magazine.</li>
								  <li>2. Products will be uploaded by the vendor.</li>
								  <li>3. Products to be delivered by vendor.</li>
								  <li>4. No interference form the PastCart team.</li>
								</ul>
								<div class="form-check">
								  <input class="form-check-input" type="radio" name="commission" id="commissionD" value="10">
								  <label class="form-check-label" for="commissionD">
									Select Plan
								  </label>
								</div>
							  </div>
							</div>
						  </div>
						  <div class="col-md-4" >
							<div class="card mb-4 rounded-3 shadow-sm" id="cardE">
							  <div class="card-header py-3">
								<h1 class="my-0 fw-normal">Plan E</h1>
							  </div>
							  <div class="card-body">
								<p>Vendor -70%<br>
PastCart -30%<br>
* Vendor doesnot have the GST number.The GST will be filed by the PastCart</p>
<h5>Highlights</h5>
								<ul class="list-unstyled mt-3 mb-4">
								  <li>1. Includes everything except old books, comics and magazine.</li>
								  <li>2. Products will be uploaded by the vendors.</li>
								  <li>3. Products to be delivered by the  vendors.</li>
								  <li>4. No interference form the PastCart team.</li>
								</ul>
								<div class="form-check">
								  <input class="form-check-input" type="radio" name="commission" id="commissionE" value="30">
								  <label class="form-check-label" for="commissionE">
									Select Plan
								  </label>
								</div>
							  </div>
							</div>
						  </div>
						  <div class="col-md-4" >
							<div class="card mb-4 rounded-3 shadow-sm" id="cardF">
							  <div class="card-header py-3">
								<h1 class="my-0 fw-normal">Plan F</h1>
							  </div>
							  <div class="card-body">
								<p>Vendor -90%<br>
PastCart -10%<br>
* Vendors  have their own GST number.The GST will be filed by the concerned vendor</p>
<h5>Highlights</h5>
								<ul class="list-unstyled mt-3 mb-4">
								  <li>1. Includes everything .</li>
								  <li>2. Products will be uploaded by the vendors.</li>
								  <li>3. Products to be delivered by the  vendors.</li>
								  <li>4. No interference form the PastCart team.</li>
								</ul>
								<div class="form-check">
								  <input class="form-check-input" type="radio" name="commission" id="commissionF" value="10">
								  <label class="form-check-label" for="commissionF">
									Select Plan
								  </label>
								</div>
							  </div>
							</div>
						  </div>
						</div>
						<button type="button" class="btn btn-primary" id="nextToStep2">Next</button>
					</div>
					<div id="step2" style="display: none;">
						@if (!Auth::check())
							<div class="bg-white rounded shadow-sm mb-3">
								<div class="fs-24 fw-600 p-3 border-bottom">
									{{ translate('Personal Info')}}
								</div>
								<div class="p-3">
									<div class="form-group">
										<label>{{ translate('Your Name')}} <span class="text-primary">*</span></label>
										<input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') }}" placeholder="{{  translate('Name') }}" name="name" required>
									</div>
									<div class="form-group">
										<label>{{ translate('Phone No.')}} <span class="text-primary">*</span></label>
										<input type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="{{ translate('Your Phone')}}" name="phone" required>
									</div>
									<div class="form-group">
										<label>{{ translate('Your Email')}} <span class="text-primary">*</span></label>
										<input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email" required>
									</div>
									<div class="form-group">
										<label>{{ translate('Your Password')}} <span class="text-primary">*</span></label>
										<input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{  translate('Password') }}" name="password" required>
									</div>
									<div class="form-group">
										<label>{{ translate('Repeat Password')}} <span class="text-primary">*</span></label>
										<input type="password" class="form-control" placeholder="{{  translate('Confirm Password') }}" name="password_confirmation" required>
									</div>
								</div>
							</div>
						@endif
						<div class="bg-white rounded shadow-sm mb-4">
							<div class="fs-24 fw-600 p-3 border-bottom">
								{{ translate('Basic Info')}}
							</div>
							<div class="p-3">
								<div class="form-group">
									<label>{{ translate('Shop Name')}} <span class="text-primary">*</span></label>
									<input type="text" class="form-control" placeholder="{{ translate('Shop Name')}}" name="name" required>
								</div>
								<div class="form-group">
									<label>{{ translate('Address')}} <span class="text-primary">*</span></label>
									<input type="text" class="form-control mb-3" placeholder="{{ translate('Address')}}" name="address" required>
								</div>
								<div class="fs-24 fw-600">{{ translate('Upload ID')}}</div>	
								<div class="form-group">
									<label>{{ translate('Please Upload Aadhaar')}} <span class="text-primary">*</span></label>
									<input type="file" class="form-control mb-3" name="idfront" required>
								</div>
								<div class="form-group">
									<label>{{ translate('Please Upload Bank Account,Passbook and cheque photo')}} <span class="text-primary">*</span></label>
									<input type="file" class="form-control mb-3" name="idback" required>
								</div>
								<div class="form-group">
									<input type="checkbox" name="checkbox_example_1" required="">
									<span class="opacity-60">By signing up you agree to our <a href="http://pastcart.shop/terms">terms and conditions</a>.</span>
								</div>
								{{-- <div class="form-group row">
									<label>{{translate('Preferred Delivery Partner')}} <span class="text-primary">*</span></label>
									<div class="">
										<select name="delivery_partner[]" class="form-control aiz-selectpicker" multiple data-max-options="3" data-live-search="true" required>
											<option value="indiapost">{{ translate('India Post') }}</option>
											<option value="delhivery">{{ translate('Delhivery') }}</option>
											<option value="pickrr">{{ translate('Pickrr') }}</option>
										</select>
									</div>
								</div>--}}
							</div>
						</div>

						@if(get_setting('google_recaptcha') == 1)
							<div class="form-group mt-2 mx-auto row">
								<div class="g-recaptcha" data-sitekey="{{ env('CAPTCHA_KEY') }}"></div>
							</div>
						@endif
						
						<div class="text-right">
							<button type="submit" class="btn btn-primary fw-600">{{ translate('Register Your Shop')}}</button>
						</div>
					</div>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection

@section('script')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script type="text/javascript">
  const cards = {
    'commissionA': document.getElementById('cardA'),
    'commissionB': document.getElementById('cardB'),
    'commissionC': document.getElementById('cardC'),
    'commissionD': document.getElementById('cardD'),
    'commissionE': document.getElementById('cardE'),
    'commissionF': document.getElementById('cardF')
  };

  document.querySelectorAll('input[name="commission"]').forEach(radio => {
    radio.addEventListener('change', function() {
      for (const key in cards) {
        cards[key].classList.remove('border-primary');
        cards[key].querySelector('.card-header').classList.remove('bg-primary');
        cards[key].querySelector('.card-header').classList.remove('border-primary');
        cards[key].querySelector('.card-header').classList.remove('text-white');
      }
      const selectedCard = cards[this.value];
      selectedCard.classList.add('border-primary');
      selectedCard.querySelector('.card-header').classList.add('bg-primary');
      selectedCard.querySelector('.card-header').classList.add('border-primary');
      selectedCard.querySelector('.card-header').classList.add('text-white');
    });
  });
  var nextToStep2 = document.getElementById('nextToStep2');
  nextToStep2.addEventListener('click', function () {
    var step1 = document.getElementById('step1');
    var step2 = document.getElementById('step2');
    step1.style.display = 'none';
    step2.style.display = 'block';
  });
    // making the CAPTCHA  a required field for form submission
    $(document).ready(function(){
        $("#shop").on("submit", function(evt)
        {
            var response = grecaptcha.getResponse();
            if(response.length == 0)
            {
            //reCaptcha not verified
                alert("Please verify you are human!");
                evt.preventDefault();
                return false;
            }
            //captcha verified
            //do the rest of your validations here
            $("#reg-form").submit();
        });
    });
</script>
@endsection
