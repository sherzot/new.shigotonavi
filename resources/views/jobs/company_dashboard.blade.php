@extends('layouts.layout')

@section('title', '企業ダッシュボード')

@section('content')
<div class="row column_title">
   <div class="col-md-12">
      <div class="page_title">
         <a href="{{ route('company.dashboard') }}"><img class="img-responsive" src="{{ asset('img/logo02.png') }}" alt="#" style="width: 150px;"/></a>
      </div>
   </div>
</div>
<!-- row -->
<div class="row column1">
   <div class="col-md-12">
      <div class="white_shd full margin_bottom_30">
         <div class="row column1">
            <div class="col-md-12">
               <div class="white_shd full margin_bottom_30">
                  <div class="full graph_head">
                     <div class="heading1 margin_0">
                        <h2>企業情報</h2>
                     </div>
                  </div>
                  <div class="full price_table padding_infor_info">
                     <div class="row">
                        <!-- column contact --> 
                        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 profile_details margin_bottom_30">
                           <div class="contact_blog">
                              <h4 class="brief">{{ $companyUser->company_p }}</h4>
                              <div class="contact_inner">
                                 <div class="left">
                                    <h3>{{ $companyUser->company_name_k }}</h3>
                                    <p><i class="fa-solid fa-map-location"></i> : {{ $companyUser->prefecture }} {{ $companyUser->city_k }} {{ $companyUser->town }} {{ $companyUser->address }}</p>
                                    <ul class="list-unstyled">
                                       <li><i class="fa-solid fa-envelope"></i> : {{ $companyUser->mailaddr}} </li>
                                       <li><i class="fa fa-phone"></i> : {{ $companyUser->telephone_number }} </li>
                                       <li><i class="fa-solid fa-hashtag"></i> : {{ $companyUser->company_code }} </li>
                                       <li><i class="fa-solid fa-clock"></i> : {{ $companyUser->created_at }} </li>
                                    </ul>
                                 </div>
                                 <div class="bottom_list">
                                    <div class="right_button">
                                       <button type="button" class="btn btn-success btn-xs"> <i class="fa fa-user">
                                       </i> <i class="fa fa-comments-o"></i> 
                                       </button>
                                       <button type="button" class="btn btn-primary btn-xs">
                                       <i class="fa-regular fa-file"></i> 詳細を見る
                                       </button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <!-- end column contact blog -->
                     </div>
                  </div>
               </div>
            </div>
            <!-- end row -->
         </div>
      </div>
   </div>
</div>
@endsection
