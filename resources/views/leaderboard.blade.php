@php($pageTitle = 'Classement')
@extends('layouts.app')
@section('content')
<main id="main-content" class="page-width content-page"><h1>Classement</h1><p class="content-intro">Les dix joueurs avec le plus de points.</p>
    <div class="table-scroll"><table><caption class="sr-only">Classement des joueurs par points</caption><thead><tr><th scope="col">Rang</th><th scope="col">Joueur</th><th scope="col">Points</th></tr></thead><tbody>
        @php($lastPoints=null) @php($lastRank=0)
        @forelse($top10 as $index=>$player)@php($rank=$lastPoints===$player['points'] ? $lastRank : $index+1)@php($lastPoints=$player['points'])@php($lastRank=$rank)
            <tr @class(['current-player'=>(session('user')->id ?? null)===$player['id']])><td><span class="rank-badge">{{ $rank<=3 ? ['🥇','🥈','🥉'][$rank-1] : str_pad($rank,2,'0',STR_PAD_LEFT) }}</span></td><td><div class="leaderboard-name"><span class="avatar-mark">{{ mb_substr($player['name'],0,1) }}</span><div><a href="{{ url('/user/profile') }}?id={{ $player['id'] }}">{{ $player['name'] }}</a><small>Niveau {{ $player['lvl'] }}</small></div></div></td><td><strong>{{ number_format($player['points'],0,',',' ') }}</strong> pts</td></tr>
        @empty<tr><td colspan="3" class="admin-empty">Aucun joueur.</td></tr>@endforelse
        @if($apartUser)<tr class="current-player"><td>{{ $position }}</td><td><a href="{{ url('/user/profile') }}?id={{ $apartUser->id }}">{{ $apartUser->name }} · vous</a></td><td><strong>{{ number_format($apartUser->points,0,',',' ') }}</strong> pts</td></tr>@endif
    </tbody></table></div>
</main>
@endsection
