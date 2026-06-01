@extends('adminlte::page')

@section('title', 'Invoice Details')

@section('content_header')
    <h1>Invoice Details</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Invoice #{{ $invoice->invoice_number }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><strong>Invoice Information</strong></h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td style="width: 150px;"><strong>Invoice Number:</strong></td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Invoice Date:</strong></td>
                                    <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Due Date:</strong></td>
                                    <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($invoice->status == 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($invoice->status == 'paid')
                                            <span class="badge badge-success">Paid</span>
                                        @else
                                            <span class="badge badge-danger">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5><strong>Customer Information</strong></h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td style="width: 150px;"><strong>Name:</strong></td>
                                    <td>
                                        {{ $invoice->customer->customer_type === 'company' ? $invoice->customer->company_name : $invoice->customer->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $invoice->customer->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $invoice->customer->phone ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($invoice->booking)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5><strong>Booking Details</strong></h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td style="width: 150px;"><strong>Booking ID:</strong></td>
                                        <td>#{{ $invoice->booking->id }}</td>
                                        <td style="width: 150px;"><strong>Vehicle:</strong></td>
                                        <td>{{ $invoice->booking->vehicle->name }} ({{ $invoice->booking->vehicle->registration_number }})</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Rental Period:</strong></td>
                                        <td>{{ $invoice->booking->from_date->format('d M Y') }} to {{ $invoice->booking->to_date->format('d M Y') }}</td>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            @if($invoice->booking->status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($invoice->booking->status == 'confirmed')
                                                <span class="badge badge-primary">Confirmed</span>
                                            @elseif($invoice->booking->status == 'on_hold')
                                                <span class="badge badge-info">On Hold</span>
                                            @else
                                                <span class="badge badge-danger">Cancelled</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><strong>Financial Details</strong></h5>
                            <table class="table table-bordered">
                                <tr>
                                    <td style="width: 200px;"><strong>Subtotal:</strong></td>
                                    <td class="text-right">{{ number_format((float)$invoice->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>VAT ({{ number_format((float)$invoice->vat, 2) }}):</strong></td>
                                    <td class="text-right">{{ number_format((float)$invoice->vat, 2) }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total Amount:</strong></td>
                                    <td class="text-right"><strong>{{ number_format((float)$invoice->total, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>

                        @if($invoice->description)
                            <div class="col-md-6">
                                <h5><strong>Notes/Description</strong></h5>
                                <p>{{ $invoice->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View All Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop