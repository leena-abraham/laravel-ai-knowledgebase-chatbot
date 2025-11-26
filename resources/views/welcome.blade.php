@extends('layouts.app')

@section('title', 'AI-Powered Support Chatbot Platform')

@section('content')
<style>
    .hero {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        padding: 40px 20px;
    }
    
    .hero-content {
        max-width: 800px;
    }
    
    .hero h1 {
        font-size: 56px;
        font-weight: 800;
        margin-bottom: 24px;
        line-height: 1.2;
    }
    
    .hero p {
        font-size: 24px;
        margin-bottom: 40px;
        opacity: 0.95;
    }
    
    .cta-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-large {
        padding: 16px 32px;
        font-size: 18px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-white {
        background: white;
        color: #6366f1;
    }
    
    .btn-white:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(255, 255, 255, 0.3);
    }
    
    .btn-outline {
        background: transparent;
        color: white;
        border: 2px solid white;
    }
    
    .btn-outline:hover {
        background: white;
        color: #6366f1;
    }
    
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 32px;
        margin-top: 80px;
    }
    
    .feature-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 32px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-8px);
        background: rgba(255, 255, 255, 0.15);
    }
    
    .feature-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }
    
    .feature-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 12px;
    }
    
    .feature-description {
        opacity: 0.9;
        line-height: 1.6;
    }
</style>

<div class="hero">
    <div class="hero-content">
        <h1>AI-Powered Support Chatbot for Your Business</h1>
        <p>
            Transform your customer support with intelligent AI chatbots trained on your knowledge base. 
            Instant answers, 24/7 availability, zero wait time.
        </p>
        
        <div class="cta-buttons">
            <a href="{{ route('register') }}" class="btn-large btn-white">
                Get Started Free
            </a>
            <a href="{{ route('login') }}" class="btn-large btn-outline">
                Sign In
            </a>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <div class="feature-title">AI-Powered Responses</div>
                <div class="feature-description">
                    Advanced GPT models provide accurate, contextual answers from your knowledge base
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <div class="feature-title">Knowledge Base</div>
                <div class="feature-description">
                    Upload PDFs, docs, and FAQs. AI automatically indexes and learns from your content
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Instant Setup</div>
                <div class="feature-description">
                    Add our chat widget to your website with just one line of code
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Analytics Dashboard</div>
                <div class="feature-description">
                    Track conversations, measure AI accuracy, and improve customer satisfaction
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <div class="feature-title">Secure & Private</div>
                <div class="feature-description">
                    Your data is encrypted and isolated. Each company has its own knowledge base
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <div class="feature-title">Affordable Pricing</div>
                <div class="feature-description">
                    Start free, scale as you grow. No hidden fees or long-term contracts
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
