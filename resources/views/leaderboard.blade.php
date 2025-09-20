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
        <table class="table table-dark table-striped text-center align-middle">
            <thead>
                <tr>
                    <th class="pe-5" scope="col">#</th>
                    <th class="text-start ps-5" scope="col">Nom</th>
                    <th scope="col">Points</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($top10 as $index => $user)
                <?php
                    $medalColor = "";
                    if($index + 1 == 1){ $medalColor = "text-gold";}
                    if($index + 1 == 2){ $medalColor = "text-silver";}
                    if($index + 1 == 3){ $medalColor = "text-bronze";}
                ?>
                    <tr>
                        <th class="pe-5 {{ $connectedUserId == $user['id'] ? "bg-" . $user['profileColor'] : ""}} {{ $medalColor }}"
                            scope="row">{{ $index + 1 }}</th>
                        <td
                            class="user-cell text-start ps-5 {{ $connectedUserId == $user['id'] ? "bg-" . $user['profileColor'] : ""}}">
                            <i class="fa-solid {{ $user['profileImage'] }} pfp-{{ $user['profileColor'] }} fs-4 me-2"></i>
                            <a class="text-decoration-none"
                                href="/user/profile?id={{ $user['id'] }}">{{ $user['name'] }}</a>
                            <div class="user-modal hidden">{{ $user['bio'] != "" ? $user['bio'] : "Pas de bio"}}</div>
                        </td>
                        <td class="{{ $connectedUserId == $user['id'] ? "bg-" . $user['profileColor'] : ""}}">
                            {{ $user['points'] }}</td>
                    </tr>
                @endforeach
                @if($apartUser != null)
                    <tr>
                        <td colspan="3" class="text-center">...</td>
                    </tr>
                    <tr class="fw-bold">
                        <th class="pe-5 bg-{{ $apartUser->profileColor}}" scope="row">{{ $position }}</th>
                        <td class="user-cell text-start ps-5 bg-{{ $apartUser->profileColor }}">
                            <i
                                class="fa-solid {{ $apartUser->profileImage }} pfp-{{ $apartUser->profileColor }} fs-4 me-2"></i>
                            <a class="text-decoration-none"
                                href="/user/profile?id={{ $apartUser->id }}">{{ $apartUser->name }}</a>
                            <div class="user-modal hidden">{{ $apartUser->bio != "" ? $apartUser->bio : "Pas de bio"}}</div>
                        </td>
                        <td class="bg-{{ $apartUser->profileColor}}">{{ $apartUser->points }}</td>
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