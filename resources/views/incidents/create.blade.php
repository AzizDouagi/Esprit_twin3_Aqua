@extends('layouts.admin')

@section('title', 'Ajouter un incident')
@section('page-title', 'Ajouter un incident')
@section('eyebrow', 'Suivi opérationnel')

@section('content')
    <section class="bg-white border rounded-2 p-4">
        <form action="{{ route('incidents.store') }}" method="POST">
            @include('incidents._form')
        </form>
    </section>
@endsection
