@extends('inc.base')
@section('content')

      @include('inc.dashboard', [
        'business_actice_info' => $business_actice_info,

    ])
@endsection


