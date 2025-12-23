{{-- Start: Paypal Area --}}





@if (count($onlineGateways) > 0)
    @foreach ($onlineGateways as $onlineGateway)
        @if ($onlineGateway->keyword == 'paypal')
            @php $route = route('product.paypal.submit'); @endphp
        @elseif($onlineGateway->keyword == 'stripe')
            @php $route = route('product.stripe.submit'); @endphp
        @elseif($onlineGateway->keyword == 'paystack')
            @php $route = route('product.paystack.submit'); @endphp
        @elseif($onlineGateway->keyword == 'flutterwave')
            @php $route = route('product.flutterwave.submit'); @endphp
        @elseif($onlineGateway->keyword == 'razorpay')
            @php $route = route('product.razorpay.submit'); @endphp
        @elseif($onlineGateway->keyword == 'instamojo')
            @php $route = route('product.instamojo.submit'); @endphp
        @elseif($onlineGateway->keyword == 'paytm')
            @php $route = route('product.paytm.submit'); @endphp
        @elseif($onlineGateway->keyword == 'mollie')
            @php $route = route('product.mollie.submit'); @endphp
        @elseif($onlineGateway->keyword == 'mercadopago')
            @php $route = route('product.mercadopago.submit'); @endphp
        @elseif($onlineGateway->keyword == 'midtrans')
            @php $route = route('product.midtrans.submit'); @endphp
        @elseif($onlineGateway->keyword == 'iyzico')
            @php $route = route('product.iyzico.submit'); @endphp
        @elseif($onlineGateway->keyword == 'paytabs')
            @php $route = route('product.paytabs.submit'); @endphp
        @elseif($onlineGateway->keyword == 'toyyibpay')
            @php $route = route('product.toyyibpay.submit'); @endphp
        @elseif($onlineGateway->keyword == 'phonepe')
            @php $route = route('product.phonepe.submit'); @endphp
        @elseif($onlineGateway->keyword == 'yoco')
            @php $route = route('product.yoco.submit'); @endphp
        @elseif($onlineGateway->keyword == 'myfatoorah')
            @php $route = route('product.myfatoorah.submit'); @endphp
        @elseif($onlineGateway->keyword == 'xendit')
            @php $route = route('product.xendit.submit'); @endphp
        @elseif($onlineGateway->keyword == 'perfect_money')
            @php $route = route('product.perfect_money.submit'); @endphp
        @endif

        <div class="option-block">
            <div class="radio-block">
                <div class="checkbox">
                    <label>
                        <input name="gateway" type="radio" class="input-check"
                            {{ old('gateway') == $onlineGateway->keyword ? 'checked="checked"' : '' }}
                            value="{{ $onlineGateway->keyword }}" data-tabid="{{ $onlineGateway->keyword }}"
                            data-action="{{ $route }}">
                        <span>{{ __($onlineGateway->name) }}</span>
                    </label>
                </div>
            </div>
        </div>

        @if ($onlineGateway->keyword == 'stripe')
            <div class="gateway-details" id="tab-stripe">
                {{-- <div id="stripe-form" class="mb-2 d-none"> --}}
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors -->
            <div id="stripe-errors" role="alert" class="mb-2"></div>
        @endif

        @if ($onlineGateway->keyword == 'iyzico')
            <div class="row gateway-details {{ old('gateway') == 'iyzico' ? '' : 'd-none' }}" id="tab-iyzico">
                <div class="col-md-6">
                    <div class="field-label">{{ __('Identity Number') }} *</div>
                    <div class="field-input">
                        <input type="text" class="card-elements mb-0" name="identity_number"
                            placeholder="{{ __('Identity Number') }}" autocomplete="off"
                            value="{{ old('identity_number') }}" />
                    </div>
                    @error('identity_number')
                        <p class="text-danger">{{ convertUtf8($message) }}</p>
                    @enderror
                    <span id="errCard" class="text-danger mb-0"></span>
                </div>
                <div class="col-md-6 on_tble_info d-none">
                    <div class="field-label">{{ __('Country') }} *</div>
                    <div class="field-input">
                        <input type="text" class="card-elements mb-0" name="country"
                            placeholder="{{ __('Country') }}" autocomplete="off" value="{{ old('country') }}" />
                    </div>
                    @error('country')
                        <p class="text-danger">{{ convertUtf8($message) }}</p>
                    @enderror
                    <span id="errCard" class="text-danger mb-0"></span>
                </div>
                <div class="col-md-6 on_tble_info d-none">
                    <div class="field-label">{{ __('city') }} *</div>
                    <div class="field-input">
                        <input type="text" class="card-elements mb-0" name="city"
                            placeholder="{{ __('city') }}" autocomplete="off" value="{{ old('city') }}" />
                    </div>
                    @error('city')
                        <p class="text-danger">{{ convertUtf8($message) }}</p>
                    @enderror
                    <span id="errCard" class="text-danger mb-0"></span>
                </div>
                <div class="col-md-6 on_tble_info d-none">
                    <div class="field-label">{{ __('address') }} *</div>
                    <div class="field-input">
                        <input type="text" class="card-elements mb-0" name="address"
                            placeholder="{{ __('address') }}" autocomplete="off" value="{{ old('address') }}" />
                    </div>
                    @error('address')
                        <p class="text-danger">{{ convertUtf8($message) }}</p>
                    @enderror
                    <span id="errCard" class="text-danger mb-0"></span>
                </div>
                <div class="col-md-6 on_tble_info d-none">
                    <div class="field-label">{{ __('zipcode') }} *</div>
                    <div class="field-input">
                        <input type="text" class="card-elements mb-0" name="zipcode"
                            placeholder="{{ __('zipcode') }}" autocomplete="off" value="{{ old('zipcode') }}" />
                    </div>
                    @error('zipcode')
                        <p class="text-danger">{{ convertUtf8($message) }}</p>
                    @enderror
                    <span id="errCard" class="text-danger mb-0"></span>
                </div>
            </div>
        @endif
    @endforeach
@endif




{{-- Start: Offline Gateways Area --}}
@foreach ($ogateways as $ogateway)
    <div class="offline" id="offline{{ $ogateway->id }}">
        <div class="option-block">
            <div class="checkbox">
                <label>
                    <input  {{ old('gateway') == $ogateway->id ? 'checked="checked"' : '' }} name="gateway" class="input-check" type="radio" value="{{ $ogateway->id }}"
                        data-tabid="{{ $ogateway->id }}"
                        data-action="{{ route('product.offline.submit', $ogateway->id) }}">
                    <span>{{ $ogateway->name }}</span>
                </label>
            </div>
        </div>

        @if (!empty($ogateway->short_description))
            <p class="gateway-desc">{!! nl2br(replaceBaseUrl(convertUtf8($ogateway->short_description))) !!}</p>
        @endif

        <div class="gateway-details row" id="tab-{{ $ogateway->id }}">

            @if (!empty(strip_tags($ogateway->instructions)))
                <div class="col-12">
                    <div class="gateway-instruction">
                        {!! nl2br(replaceBaseUrl(convertUtf8($ogateway->instructions))) !!}
                    </div>
                </div>
            @endif

            @if ($ogateway->is_receipt == 1)
                <div class="col-12 mb-4">
                    <label for="" class="d-block">{{ __('Receipt') }} **</label>
                    <input type="file" name="receipt">
                    <p class="mb-0 text-warning">** {{ __('Receipt image must be .jpg / .jpeg / .png') }}</p>
                </div>
            @endif

            @if ($errors->has('receipt'))
                <p class="text-danger mb-4">{{ $errors->first('receipt') }}</p>
            @endif

        </div>  
    </div>
@endforeach


{{-- End: Offline Gateways Area --}}
