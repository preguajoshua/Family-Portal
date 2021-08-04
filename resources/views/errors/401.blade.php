@extends('errors::illustrated-layout')

@section('title', __('Unauthorised'))
@section('code', '401')
@section('message', ( $exception->getMessage()))
