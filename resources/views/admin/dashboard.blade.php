@include('shared.header')
@include('shared.navbar')

<h1 class="mt-5 text-center">Tableau de bord</h1>

<div class="d-flex justify-content-center mt-3 mb-4">
    <input type="text" id="dashboardSearch" 
           class="form-control w-25" 
           placeholder="Rechercher par nom">
</div>

<table class="table table-dark table-striped mt-3" id="usersTable">
    <thead>
        <tr>
            <th scope="col"></th>
            <th scope="col">Nom d'utilisateur</th>
            <th scope="col">Points</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td><i class="fas {{ $user['profileImage'] . " pfp-" . $user['profileColor'] }} fs-2 ms-4"></i></td>
                <td class="username">{{ $user["name"] }}</td>
                <td>
                    {{ $user["points"] }}
                    <form action="/admin/fixPoints" method="post" class="d-inline">
                        @csrf
                        <input type="hidden" name="userId" value="{{ $user['id'] }}">
                        <input type="number" name="points" value="{{ $user['points'] }}" 
                               class="form-control d-inline-block w-auto" style="width: 80px;">
                        <button type="submit" class="btn btn-danger btn-sm">mettre à jour</button>
                    </form>
                </td>
                <td>
                    <form action="/admin/toggleBan" method="post">
                        @csrf
                        <input type="hidden" name="userId" value="{{ $user['id'] }}">
                        @if($user['banned'])
                            <button type="submit" class="btn btn-success" name="action" value="unban">Débannir</button>
                        @else
                            <button type="submit" class="btn btn-danger" name="action" value="ban">Bannir</button>
                        @endif
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
$(document).ready(function () {
    $('#dashboardSearch').on('input', function () {
        const filter = this.value.toLowerCase();
        $('#usersTable tbody tr').each(function () {
            const username = $(this).find('.username').text().toLowerCase();
            if (username.includes(filter)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>



@include('shared.footer')

