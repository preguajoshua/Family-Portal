<!doctype html>
<html lang="en">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

        <title>Fake Axxess SSO</title>
    </head>

    <body>
        <div class="d-flex justify-content-between">

            <div class="vh-100 flex-fill bg-light d-flex align-items-center">
                <div class="card mx-auto w-50">
                    <div class="card-body text-center">
                        <div class="my-4">
                            <img src="https://accounts.axxessweb.com/Images/Login/logo.svg" alt="Axxess" height="60">
                        </div>
                        <h5 class="my-1">Select a user:</h5>
                        <ul class="my-3 list-unstyled">
                            @forelse ($users as $user)
                            <li class="my-1">
                                <a href="{% route('fake-sso-login', $user) %}"
                                    class="btn btn-outline-info btn-sm"
                                    role="button"
                                >
                                    {% $user->name %} ({% $user->email %})
                                </a>
                            </li>
                            @empty
                                <li class="my-1">
                                    <div class="alert alert-danger" role="alert">
                                        No available users.
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="vh-100 flex-fill bg-info d-flex align-items-center">
                <div class="mx-auto">
                    <h1 class="text-white display-3">Fake Axxess SSO</h1>
                </div>
            </div>

        </div>
    </body>
</html>
