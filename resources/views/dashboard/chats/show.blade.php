@extends('layouts.dashboard')

@section('page-title', 'Conversation Details')

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('chats.index') }}" style="color: #6366f1; text-decoration: none;">
        ← Back to Conversations
    </a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <div class="card">
        <h3 style="margin-bottom: 24px; color: #1f2937;">Chat Messages</h3>
        
        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($chat->messages as $message)
                @if($message->role === 'user')
                    <div style="display: flex; justify-content: flex-end;">
                        <div style="background: #6366f1; color: white; padding: 12px 16px; border-radius: 16px 16px 0 16px; max-width: 70%;">
                            <div style="font-size: 14px; line-height: 1.6;">{{ $message->content }}</div>
                            <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">
                                {{ $message->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                @elseif($message->role === 'assistant')
                    <div style="display: flex; justify-content: flex-start;">
                        <div style="background: #f3f4f6; color: #1f2937; padding: 12px 16px; border-radius: 16px 16px 16px 0; max-width: 70%;">
                            <div style="font-size: 14px; line-height: 1.6;">{{ $message->content }}</div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span>{{ $message->created_at->format('H:i') }}</span>
                                @if($message->model_used)
                                    <span>{{ $message->model_used }}</span>
                                @endif
                            </div>
                            
                            @if($message->was_helpful !== null)
                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e5e7eb;">
                                    @if($message->was_helpful)
                                        <span style="color: #10b981;">👍 Marked as helpful</span>
                                    @else
                                        <span style="color: #ef4444;">👎 Marked as not helpful</span>
                                    @endif
                                    @if($message->feedback)
                                        <div style="font-style: italic; margin-top: 4px; color: #6b7280;">
                                            "{{ $message->feedback }}"
                                        </div>
                                    @endif
                                </div>
                            @endif
                            
                            @if($message->context_chunks && count($message->context_chunks) > 0)
                                <div style="margin-top: 8px; font-size: 12px; color: #6b7280;">
                                    📚 Used {{ count($message->context_chunks) }} knowledge base chunks
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    
    <div>
        <div class="card">
            <h4 style="margin-bottom: 16px; color: #1f2937;">Chat Information</h4>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Session ID</div>
                    <code style="font-size: 12px; display: block; margin-top: 4px; word-break: break-all;">
                        {{ $chat->session_id }}
                    </code>
                </div>
                
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Customer</div>
                    <div style="font-weight: 600; margin-top: 4px;">
                        {{ $chat->customer_name ?? 'Anonymous' }}
                    </div>
                    @if($chat->customer_email)
                        <div style="font-size: 14px; color: #6b7280;">{{ $chat->customer_email }}</div>
                    @endif
                </div>
                
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Started</div>
                    <div style="margin-top: 4px;">{{ $chat->started_at->format('M d, Y H:i') }}</div>
                </div>
                
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Last Activity</div>
                    <div style="margin-top: 4px;">
                        {{ $chat->last_message_at ? $chat->last_message_at->diffForHumans() : 'N/A' }}
                    </div>
                </div>
                
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Total Messages</div>
                    <div style="font-weight: 600; margin-top: 4px;">{{ $chat->message_count }}</div>
                </div>
                
                <div>
                    <div style="color: #6b7280; font-size: 14px;">Status</div>
                    <div style="margin-top: 4px;">
                        @if($chat->is_resolved)
                            <span class="badge badge-success">Resolved</span>
                        @else
                            <span class="badge badge-warning">Active</span>
                        @endif
                    </div>
                </div>
                
                @if($chat->customer_ip)
                    <div>
                        <div style="color: #6b7280; font-size: 14px;">IP Address</div>
                        <div style="margin-top: 4px; font-size: 14px;">{{ $chat->customer_ip }}</div>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card" style="margin-top: 16px;">
            <h4 style="margin-bottom: 16px; color: #1f2937;">Statistics</h4>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @php
                    $userMessages = $chat->messages->where('role', 'user')->count();
                    $assistantMessages = $chat->messages->where('role', 'assistant')->count();
                    $helpfulCount = $chat->messages->where('was_helpful', true)->count();
                    $unhelpfulCount = $chat->messages->where('was_helpful', false)->count();
                @endphp
                
                <div style="background: #f9fafb; padding: 12px; border-radius: 8px;">
                    <div style="color: #6b7280; font-size: 14px;">User Messages</div>
                    <div style="font-weight: 600; font-size: 20px; margin-top: 4px;">{{ $userMessages }}</div>
                </div>
                
                <div style="background: #f9fafb; padding: 12px; border-radius: 8px;">
                    <div style="color: #6b7280; font-size: 14px;">AI Responses</div>
                    <div style="font-weight: 600; font-size: 20px; margin-top: 4px;">{{ $assistantMessages }}</div>
                </div>
                
                @if($helpfulCount > 0 || $unhelpfulCount > 0)
                    <div style="background: #f9fafb; padding: 12px; border-radius: 8px;">
                        <div style="color: #6b7280; font-size: 14px;">Feedback</div>
                        <div style="margin-top: 4px;">
                            <span style="color: #10b981;">👍 {{ $helpfulCount }}</span>
                            <span style="margin-left: 12px; color: #ef4444;">👎 {{ $unhelpfulCount }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
