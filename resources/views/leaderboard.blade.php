@include('shared.header')
@include('shared.navbar')
<?php
$connectedUserId = 0;
if ($userConnected = session()->has("user")) {
    $connectedUser = session('user');
    $connectedUserId = $connectedUser->id;
}
?>
<h1 class="mt-5 text-center">Classement Général</h1>

<div class="container mt-5">
    <div>
        <table class="table table-dark text-center align-middle">
            <thead>
                <tr>
                    <th class="pe-5" scope="col">#</th>
                    <th class="text-start ps-5" scope="col">Nom</th>
                    <th scope="col">Points</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $lastPoints = null;
                    $lastRank = 0;
                @endphp

                @foreach ($top10 as $index => $user)
                    @php
                        if ($lastPoints === $user['points']) {
                            $rank = $lastRank;
                        } else {
                            $rank = $index + 1;
                            $lastRank = $rank;
                            $lastPoints = $user['points'];
                        }

                        $medalColor = "";
                        if ($rank == 1) {
                            $medalColor = "text-gold";
                        }
                        if ($rank == 2) {
                            $medalColor = "text-silver";
                        }
                        if ($rank == 3) {
                            $medalColor = "text-bronze";
                        }

                        $idMatching = $connectedUserId == $user['id'];
                        $textColors = [
                            'yellow' => 'text-dark',
                            'white' => 'text-dark',
                            'pink' => 'text-dark',
                        ];
                        $textColor = $idMatching ? ($textColors[$user['profileColor']] ?? 'text-white') : 'text-white';
                    @endphp

                    <tr>
                        <th class="pe-5 {{ $idMatching ? "bg-" . $user['profileColor'] . " " . $textColor : ""}} {{ $medalColor }}"
                            scope="row">{{ $rank }}</th>
                        <td
                            class="user-cell text-start ps-5 {{ $idMatching ? "bg-" . $user['profileColor'] . " " . $textColor : ""}}">
                            <i class="fa-solid {{ $user['profileImage'] }} pfp-{{ $user['profileColor'] }} fs-4 me-2"></i>
                            <a class="text-decoration-none {{ $textColor }}"
                                href="/user/profile?id={{ $user['id'] }}">{{ $user['name'] }}</a>
                            <div class="user-modal hidden">{{ $user['bio'] != "" ? $user['bio'] : "Pas de bio"}}</div>
                        </td>
                        <td class="{{ $idMatching ? "bg-" . $user['profileColor'] . " " . $textColor : ""}}">
                            {{ $user['points'] }}
                        </td>
                    </tr>
                @endforeach
                @php
                    if($top10[9]['points'] == $apartUser['points']){
                        $position = $lastRank;
                    }
                @endphp
                @if($apartUser != null)
                    <tr>
                        <td colspan="3" class="text-center">...</td>
                    </tr>
                    <tr class="fw-bold">
                        <th class="pe-5" scope="row">{{ $position }}</th>
                        <td class="user-cell text-start ps-5 }">
                            <i
                                class="fa-solid {{ $apartUser->profileImage }} pfp-{{ $apartUser->profileColor }} fs-4 me-2"></i>
                            <a class="text-decoration-none"
                                href="/user/profile?id={{ $apartUser->id }}">{{ $apartUser->name }}</a>
                            <div class="user-modal hidden">{{ $apartUser->bio != "" ? $apartUser->bio : "Pas de bio"}}</div>
                        </td>
                        <td>{{ $apartUser->points }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<style>
    .user-cell {
        position: relative;
    }

    .user-modal {
        position: absolute;
        top: 100%;
        left: 0;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        z-index: 10;
        background: #222;
        white-space: nowrap;
        transform: translateY(5px);
    }

    .hidden {
        display: none;
    }
</style>

<script>
    document.querySelectorAll('.user-cell').forEach(cell => {
        cell.addEventListener('mouseenter', () => {
            cell.querySelector('.user-modal').classList.remove('hidden');
        });

        cell.addEventListener('mouseleave', () => {
            cell.querySelector('.user-modal').classList.add('hidden');
        });
    });
</script>
@include('shared.footer')