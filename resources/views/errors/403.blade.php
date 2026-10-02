@extends('errors.layout')

@section('code', '403')
@section('title', 'Доступ запрещён')
@section('message', $exception->getMessage() && $exception->getMessage() !== 'This action is unauthorized.' ? $exception->getMessage() : 'У вашей роли нет доступа к этому разделу. Войдите под другим аккаунтом или вернитесь на главную.')
