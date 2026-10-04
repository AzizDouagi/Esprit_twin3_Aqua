@extends('layouts.admin')

@section('title', 'Ajouter une infrastructure')
@section('page-title', 'Ajouter une infrastructure')
@section('eyebrow', 'Gestion du réseau')

@section('content')
    <section class="bg-white border rounded-2 p-4">
        <form action="{{ route('infrastructures.store') }}" method="POST">
            @include('infrastructures._form')
        </form>
    </section>
@endsection
