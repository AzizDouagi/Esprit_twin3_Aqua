@extends('layouts.admin')

@section('title', 'Modifier une infrastructure')
@section('page-title', 'Modifier une infrastructure')
@section('eyebrow', $infrastructure->nom)

@section('content')
    <section class="bg-white border rounded-2 p-4">
        <form action="{{ route('infrastructures.update', $infrastructure) }}" method="POST">
            @method('PUT')
            @include('infrastructures._form')
        </form>
    </section>
@endsection
