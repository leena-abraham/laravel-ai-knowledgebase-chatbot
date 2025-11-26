<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;

        // Get statistics
        $stats = [
            'total_documents' => $company->documents()->count(),
            'total_chats' => $company->chats()->count(),
            'total_messages' => Message::where('company_id', $company->id)->count(),
            'monthly_chats' => $company->current_month_chats,
            'chat_limit' => $company->max_chats_per_month,
            'document_limit' => $company->max_documents,
        ];

        // Recent chats
        $recentChats = $company->chats()
            ->with('messages')
            ->latest('last_message_at')
            ->limit(10)
            ->get();

        // Chat analytics (last 7 days)
        $chatsByDay = Chat::where('company_id', $company->id)
            ->where('started_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(started_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Most asked questions (top 10)
        $topQuestions = Message::where('company_id', $company->id)
            ->where('role', 'user')
            ->select('content', DB::raw('COUNT(*) as count'))
            ->groupBy('content')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // AI success rate (helpful responses)
        $totalResponses = Message::where('company_id', $company->id)
            ->where('role', 'assistant')
            ->whereNotNull('was_helpful')
            ->count();

        $helpfulResponses = Message::where('company_id', $company->id)
            ->where('role', 'assistant')
            ->where('was_helpful', true)
            ->count();

        $successRate = $totalResponses > 0 ? round(($helpfulResponses / $totalResponses) * 100, 1) : 0;

        return view('dashboard.index', compact(
            'stats',
            'recentChats',
            'chatsByDay',
            'topQuestions',
            'successRate'
        ));
    }
}
