@extends('errors.layout')

@section('status', '419')
@section('heading', 'เซสชันหมดอายุแล้ว')
@section('message', 'เพื่อความปลอดภัย กรุณากลับไปเข้าสู่ระบบใหม่ก่อนดำเนินการต่อ ข้อมูลที่ยังไม่ได้บันทึกอาจต้องกรอกอีกครั้ง')
@section('secondary_url', route('login'))
@section('secondary_label', 'เข้าสู่ระบบอีกครั้ง')
