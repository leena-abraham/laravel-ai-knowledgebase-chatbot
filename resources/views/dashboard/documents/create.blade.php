@extends('layouts.dashboard')

@section('page-title', 'Upload Document')

@section('content')
<div class="card" style="max-width: 600px;">
    <h2 style="margin-bottom: 24px; color: #1f2937;">Upload New Document</h2>
    
    @if($errors->any())
        <div class="alert alert-error">
            <ul style="list-style: none;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label class="form-label" for="title">Document Title</label>
            <input type="text" id="title" name="title" class="form-control" 
                   value="{{ old('title') }}" required autofocus
                   placeholder="e.g., Product FAQ, User Manual, Pricing Guide">
        </div>
        
        <div class="form-group">
            <label class="form-label" for="file">File</label>
            <input type="file" id="file" name="file" class="form-control" 
                   accept=".pdf,.txt,.doc,.docx" required>
            <small style="color: #6b7280; font-size: 14px; display: block; margin-top: 8px;">
                Supported formats: PDF, TXT, DOC, DOCX (Max 10MB)
            </small>
        </div>
        
        <div style="background: #f0f9ff; padding: 16px; border-radius: 8px; margin: 24px 0; border-left: 4px solid #3b82f6;">
            <strong style="color: #1e40af;">💡 Tips for best results:</strong>
            <ul style="margin-top: 8px; color: #1e3a8a; font-size: 14px;">
                <li>Use clear, well-structured documents</li>
                <li>Include FAQs, product information, and support guides</li>
                <li>The AI will automatically split and index your content</li>
            </ul>
        </div>
        
        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary">
                Upload & Process
            </button>
            <a href="{{ route('documents.index') }}" class="btn" style="background: #f3f4f6; color: #1f2937;">
                Cancel
            </a>
        </div>
    </form>
</div>

<style>
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
</style>
@endsection
