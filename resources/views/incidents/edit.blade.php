@extends('layouts.admin')

@section('title', 'Modifier un incident')
@section('page-title', 'Modifier un incident')
@section('eyebrow', $incident->titre)

@section('content')
    <section class="bg-white border rounded-2 p-4">
        <form action="{{ route('incidents.update', $incident) }}" method="POST">
            @method('PUT')
            @include('incidents._form')
        </form>
    </section>
@endsection
