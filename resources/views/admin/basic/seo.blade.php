@extends('admin.layout')

{{-- this style will be applied when the direction of language is right-to-left --}}
@includeIf('admin.partials.rtl-style')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('SEO Informations') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('SEO Informations') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form action="{{ route('admin.seo.update', ['language' => request()->input('language')]) }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10">
                                <div class="card-title">{{ __('Update SEO Informations') }}</div>
                            </div>

                            <div class="col-lg-2">
                                    @if (!empty($langs))
                                        <select name="language" class="form-control"
                                            onchange="window.location='{{ url()->current() . '?language=' }}'+this.value">
                                            <option value="" selected disabled>Select a Language</option>
                                            @foreach ($langs as $lang)
                                                <option value="{{ $lang->code }}"
                                                    {{ $lang->code == request()->input('language') ? 'selected' : '' }}>
                                                    {{ $lang->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Home Page') }}</label>
                                    <input class="form-control" name="meta_keyword_home"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_home }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Home Page') }}</label>
                                    <textarea class="form-control" name="meta_description_home" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_home }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Menu Page') }}</label>
                                    <input class="form-control" name="meta_keyword_menu"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_menu }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Menu Page') }}</label>
                                    <textarea class="form-control" name="meta_description_menu" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_menu }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Items Page') }}</label>
                                    <input class="form-control" name="meta_keyword_item"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_item }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Items Page') }}</label>
                                    <textarea class="form-control" name="meta_description_item" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_item }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For About us Page') }}</label>
                                    <input class="form-control" name="meta_keyword_about_us"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_about_us }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For About us Page') }}</label>
                                    <textarea class="form-control" name="meta_description_about_us" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_about_us }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Career Page') }}</label>
                                    <input class="form-control" name="meta_keyword_career"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_career }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Career Page') }}</label>
                                    <textarea class="form-control" name="meta_description_career" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_career }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Team Member Page') }}</label>
                                    <input class="form-control" name="meta_keyword_team_member"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_team_member }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Team Member Page') }}</label>
                                    <textarea class="form-control" name="meta_description_team_member" rows="5"
                                        placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_team_member }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Gallery Page') }}</label>
                                    <input class="form-control" name="meta_keyword_gallery"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_gallery }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Gallery Page') }}</label>
                                    <textarea class="form-control" name="meta_description_gallery" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_gallery }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For FAQ Page') }}</label>
                                    <input class="form-control" name="meta_keyword_faq"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_faq }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For FAQ Page') }}</label>
                                    <textarea class="form-control" name="meta_description_faq" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_faq }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Blog Page') }}</label>
                                    <input class="form-control" name="meta_keyword_blog"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_blog }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Blog Page') }}</label>
                                    <textarea class="form-control" name="meta_description_blog" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_blog }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Contact Page') }}</label>
                                    <input class="form-control" name="meta_keyword_contact"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_contact }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Contact Page') }}</label>
                                    <textarea class="form-control" name="meta_description_contact" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_contact }}</textarea>
                                </div>
                            </div>


                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Reservation Page') }}</label>
                                    <input class="form-control" name="meta_keyword_reservation"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_reservation }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Reservation Page') }}</label>
                                    <textarea class="form-control" name="meta_description_reservation" rows="5"
                                        placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_reservation }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Cart Page') }}</label>
                                    <input class="form-control" name="meta_keyword_cart"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_cart }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Cart Page') }}</label>
                                    <textarea class="form-control" name="meta_description_cart" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_cart }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Checkout Page') }}</label>
                                    <input class="form-control" name="meta_keyword_checkout"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_checkout }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Checkout Page') }}</label>
                                    <textarea class="form-control" name="meta_description_checkout" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_checkout }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Login Page') }}</label>
                                    <input class="form-control" name="meta_keyword_login"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_login }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Login Page') }}</label>
                                    <textarea class="form-control" name="meta_description_login" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_login }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Signup Page') }}</label>
                                    <input class="form-control" name="meta_keyword_signup"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_signup }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Signup Page') }}</label>
                                    <textarea class="form-control" name="meta_description_signup" rows="5" placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_signup }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Forget Password Page') }}</label>
                                    <input class="form-control" name="meta_keyword_forget_password"
                                        value="{{ is_null($data) ? '' : $data->meta_keyword_forget_password }}"
                                        placeholder="Enter Meta Keywords" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Forget Password Page') }}</label>
                                    <textarea class="form-control" name="meta_description_forget_password" rows="5"
                                        placeholder="Enter Meta Description">{{ is_null($data) ? '' : $data->meta_description_forget_password }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
