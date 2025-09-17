@extends('layouts.layout')

@section('title', 'エージェントダッシュボード')

@section('content')
<div class="row column_title">
   <div class="col-md-12">
      <div class="page_title">
         <a href="{{ route('agent.dashboard') }}"><img class="img-responsive" src="{{ asset('img/logo02.png') }}" alt="#" style="width: 150px;"/></a>
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
                        <h2>エージェント情報</h2>
                     </div>
                  </div>
                  <div class="full price_table padding_infor_info">
                     <div class="row">
                        <!-- Agent ma'lumotlari mavjud bo'lsa -->
                        @if (isset($agentUser))
                        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 profile_details margin_bottom_30">
                           <div class="contact_blog">
                              <h4 class="brief">{{ $agentUser->agent_name }}</h4>
                              <div class="contact_inner">
                                 <div class="left">
                                    <h3>{{ $agentUser->agent_company_name }}</h3>
                                    <p><i class="fa-solid fa-map-location"></i> : {{ $agentUser->prefecture_code }} {{ $agentUser->city }} {{ $agentUser->town }} {{ $agentUser->address }}</p>
                                    <ul class="list-unstyled">
                                       <li><i class="fa-solid fa-envelope"></i> : {{ $agentUser->mail_address }}</li>
                                       <li><i class="fa fa-phone"></i> : {{ $agentUser->office_telephone_number }}</li>
                                       <li><i class="fa-solid fa-hashtag"></i> : {{ $agentUser->agent_code }}</li>
                                       <li><i class="fa-solid fa-clock"></i> : {{ $agentUser->created_at ?? 'N/A' }}</li>
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                        @else
                        <div class="col-12">
                           <div class="alert alert-danger text-center">
                              エージェント情報が見つかりません。
                           </div>
                        </div>
                        @endif
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
