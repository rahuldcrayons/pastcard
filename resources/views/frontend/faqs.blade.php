@extends('frontend.layouts.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css?family=Tajawal');
@import url('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');

section{
	padding: 60px 0;
}
.card-header h5{
    margin-top:0;
}
.accordion .card{
    border-bottom: 1px solid rgba(0, 0, 0, .125) !important;
    margin-bottom: 0;
}
#accordion-style-1 h1,
#accordion-style-1 a{
    color:#5089fd;
}
#accordion-style-1 .btn-link {
    font-weight: 400;
    color: #5089fd;
    background-color: transparent;
    text-decoration: none !important;
    font-size: 16px;
    font-weight: bold;
	padding-left: 25px;
}

#accordion-style-1 .card-body {
    border-top: 2px solid #007b5e;
}

#accordion-style-1 .card-header .btn .fa.main{
	background: #5089fd;
    padding: 13px 11px;
    color: #ffffff;
    width: 35px;
    height: 41px;
    position: absolute;
    left: -1px;
    top: 10px;
    border-top-right-radius: 7px;
    border-bottom-right-radius: 7px;
	display:block;
}
</style>
<section class="mb-4">
    <div class="container-fluid bg-gray" id="accordion-style-1">
	<div class="container">
			<div class="row">
				<div class="col-12">
					<h1 class="text-green mb-4 text-center">Frequently Asked Questions</h1>
				</div>
				<div class="col-10 mx-auto">
					<div class="accordion" id="accordionExample">
						<div class="card">
							<div class="card-header" id="headingOne">
								<h5 class="mb-0">
							<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>Is it a trustable site?
							</button>
						  </h5>
							</div>

							<div id="collapseOne" class="collapse show fade" aria-labelledby="headingOne" data-parent="#accordionExample">
								<div class="card-body">
									Yes Sir. We are in full swing operation since 15th August 2019.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="headingTwo">
								<h5 class="mb-0">
							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
							 <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>How many days it will take for the parcel to reach after placing the order?
							</button>
						  </h5>
							</div>
							<div id="collapseTwo" class="collapse fade" aria-labelledby="headingTwo" data-parent="#accordionExample">
								<div class="card-body">
									It will take three days (3 days) for processing the order. Thereafter, another 7 days to reach to you. In short it will take 10 days to deliver after placing the order.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="headingThree">
								<h5 class="mb-0">
							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>What would be the condition of the product?
							</button>
						  </h5>
							</div>
							<div id="collapseThree" class="collapse fade" aria-labelledby="headingThree" data-parent="#accordionExample">
								<div class="card-body">
									The product is second hand therefore it would be average. In case of Book, comics, novels and magazine there will be no missing pages. The vendor conducts a thorough inspection before packaging.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="headingFour">
								<h5 class="mb-0">
							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>Is there any discount coupon?
							</button>
						  </h5>
							</div>
							<div id="collapseFour" class="collapse fade" aria-labelledby="headingFour" data-parent="#accordionExample">
								<div class="card-body">
									No Sir, we are already working in 8 percent margin with our vendors. It is not possible to give discount coupon. However we run festival offers at the time of Diwali , Eid and Christmas.
								</div>
							</div>
						</div>
						
						<div class="card">
							<div class="card-header" id="heading5">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>What if I don’t like the additional images or videos provided to me after placing the order?
    							</button>
    						  </h5>
							</div>
							<div id="collapse5" class="collapse fade" aria-labelledby="heading5" data-parent="#accordionExample">
								<div class="card-body">
									n that case 100 percent amount will be refunded within 24 hours and the order will get cancelled.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading6">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>After how much time will I get additional images and videos? 
    							</button>
    						  </h5>
							</div>
							<div id="collapse6" class="collapse fade" aria-labelledby="heading6" data-parent="#accordionExample">
								<div class="card-body">
									Additional images/videos will be shared within three working days of placing the order.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading7">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>Can we have a monthly subscription to magazines?
    							</button>
    						  </h5>
							</div>
							<div id="collapse7" class="collapse fade" aria-labelledby="heading7" data-parent="#accordionExample">
								<div class="card-body">
									No Sir, We only sell the 2nd hand items.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading8">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>Am I allowed to sell my items on PastCart?
    							</button>
    						  </h5>
							</div>
							<div id="collapse8" class="collapse fade" aria-labelledby="heading8" data-parent="#accordionExample">
								<div class="card-body">
									Yes of course!! You can register by clicking on vendor registration link mentioned on the top right hand side. Fill the required details. Upload the photo, cancelled cheque and aadhar card(both side),  PanCard . Download the vendor agreement copy. Sign the same scan it   and send it to us on care@pastcart.com. Upon verification account will be created and will send to you. A tutorial video link will also be shared to how to do the uploading.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading9">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>I have ordered so many times. Can I get a discount coupon?
    							</button>
    						  </h5>
							</div>
							<div id="collapse9" class="collapse fade" aria-labelledby="heading9" data-parent="#accordionExample">
								<div class="card-body">
									No Sir we don’t have any discount coupon, we are already selling the items on discounted rates.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading10">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>How to track my order?
    							</button>
    						  </h5>
							</div>
							<div id="collapse10" class="collapse fade" aria-labelledby="heading10" data-parent="#accordionExample">
								<div class="card-body">
									Tracking id will be sent to your mobile inbox. In case of COD (Cash on Delivery) orders, you can visit to concerned courier partner website by just clicking the link on mobile inbox message. In case of orders sent by Indian post, the tracking id will be sent to mobile inbox from where you can visit to India post tracking id page and can track your order. The tracking facilities usually available after 5 days of placing the order.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading11">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse11" aria-expanded="false" aria-controls="collapse11">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>What if I don’t like the condition of the product or I got a damaged product?
    							</button>
    						  </h5>
							</div>
							<div id="collapse11" class="collapse fade" aria-labelledby="heading11" data-parent="#accordionExample">
								<div class="card-body">
									Sir, you can send the video of the product on the business account no of PastCart i.e. +917319975677(Watsapp no ) within 28 days of placing the order. If claim found satisfactory, 100 percent amount will be refunded. The item needs to be returned from your side. the Postal expenses will be availed by the PastCart.com.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading12">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse12" aria-expanded="false" aria-controls="collapse12">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>Can I have additional images of the products I ordered from the site?
    							</button>
    						  </h5>
							</div>
							<div id="collapse12" class="collapse fade" aria-labelledby="heading12" data-parent="#accordionExample">
								<div class="card-body">
									Yes Sir, You can have additional images of the product after placing the order. The additional images will be shared only of the product whose individual worth is more than or equal to 500.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading13">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse13" aria-expanded="false" aria-controls="collapse13">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>Can I have additional images of the products I am willing to buy?
    							</button>
    						  </h5>
							</div>
							<div id="collapse13" class="collapse fade" aria-labelledby="heading13" data-parent="#accordionExample">
								<div class="card-body">
									No Sir, Additional images or videos will only be provided wherein order is placed. You need to send the additional request for the same on registered Watsapp no of the PastCart i.e. +917319975677.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading14">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse14" aria-expanded="false" aria-controls="collapse14">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>What is the concept of the working day? I am not getting it.
    							</button>
    						  </h5>
							</div>
							<div id="collapse14" class="collapse fade" aria-labelledby="heading14" data-parent="#accordionExample">
								<div class="card-body">
									Once you place the order say on Tuesday, the additional images (wherein the images were demanded by the customers)will be displayed by Friday. Dispatch will be made on Saturday. In case there is week off or public holiday lies in-between, that day will not be counted and additional day will be taken to fulfill the order.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading15">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse15" aria-expanded="false" aria-controls="collapse15">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>What if I place the order and did not get the item?
    							</button>
    						  </h5>
							</div>
							<div id="collapse15" class="collapse fade" aria-labelledby="heading15" data-parent="#accordionExample">
								<div class="card-body">
									In that case 100 percent amount will be refunded by the PastCart and that vendor will be blocked to do any operation.
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading16">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse16" aria-expanded="false" aria-controls="collapse16">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>What if items get damaged during transportation?
    							</button>
    						  </h5>
							</div>
							<div id="collapse16" class="collapse fade" aria-labelledby="heading16" data-parent="#accordionExample">
								<div class="card-body">
									You need to send the item back to us. 100 percent amount will be refunded. 
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header" id="heading17">
								<h5 class="mb-0">
    							<button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse17" aria-expanded="false" aria-controls="collapse17">
    							  <i class="fa fa-plus main"></i><i class="fa fa-angle-double-right mr-3"></i>As a vendor, How do I know that I have an order from the customer? 
    							</button>
    						  </h5>
							</div>
							<div id="collapse17" class="collapse fade" aria-labelledby="heading17" data-parent="#accordionExample">
								<div class="card-body">
									Once the order is placed, The order details will automatically delivered to your registered mail id.
								</div>
							</div>
						</div>
					</div>
				</div>	
			</div>
	</div>
</div>    
</section>
@endsection
@section('script')
<script>
    $(document).ready(function () {
        // Add minus icon for collapse element which
        // is open by default
        $(".collapse.show").each(function () {
            $(this).prev(".card-header").find(".fa")
                .addClass("fa-minus").removeClass("fa-plus");
        });
        // Toggle plus minus icon on show hide
        // of collapse element
        $(".collapse").on('show.bs.collapse', function () {
            $(this).prev(".card-header").find(".fa")
                .removeClass("fa-plus").addClass("fa-minus");
        }).on('hide.bs.collapse', function () {
            $(this).prev(".card-header").find(".fa")
                .removeClass("fa-minus").addClass("fa-plus");
        });
    });
</script>
@endsection