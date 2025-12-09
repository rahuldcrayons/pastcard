@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">

                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('price_setting.update') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="card-header">
                            <h1 class="mb-0 h6">{{translate('New User Pricing')}}</h1>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate('Amount')}}</label>
                            <div class="col-sm-9">
                                <input type="text" name="amount" class="form-control" value="{{ $user->amount }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate(' Normal Percetange')}}</label>
                            <div class="col-sm-9">
                                <input type="text" name="percent" class="form-control" value="{{ $user->percent }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate(' Premium Percetange')}}</label>
                            <div class="col-sm-9">
                                <input type="text" name="percent_premium" class="form-control" value="{{ $user->percent_premium }}">
                            </div>
                        </div>
                        <div class="card-header">
                            <h1 class="mb-0 h6">{{translate('Shipping Pricing')}}</h1>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate('Normal Delivery')}}</label>
                            <div class="col-sm-9">
                                <input type="text" name="charge_normal" class="form-control" value="{{ $charge_normal }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate('Fast Delivery')}}</label>
                            <div class="col-sm-9">
                                <input type="text" name="charge_premium" class="form-control" value="{{ $charge_premium }}">
                            </div>
                        </div>
                        <div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
    					</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
