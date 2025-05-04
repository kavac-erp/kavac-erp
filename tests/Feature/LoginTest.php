<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends TestCase
{
    use DatabaseMigrations;
    use RefreshDatabase;

    /**
     * Verifica si puede acceder a la ruta de autenticación de usuarios
     *
     * @return void
     */
    public function testAccessToLogin()
    {
        $response = $this->get('/login');

        $response->assertStatus(200)->assertSee('login');
    }

    /**
     * Verifica si muestra el formulario de autenticación
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function testLoginDisplaysTheLoginForm()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Verifica si se obtienen mensajes de error al no indicar datos del usuario a autenticar
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function testLoginDisplaysValidationErrors()
    {
        $response = $this->post(route('login'), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('username');
        $response->assertSessionHasErrors('password');
    }

    /**
     * Verifica si un usuario puede autenticarse en la aplicación
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function testAuthenticatedUser()
    {
        putenv("TEST_UNIT=true");
        $user = User::updateOrCreate([
            "username" => "test",
        ], [
            "name" => "User Test",
            "email" => "test@mail.com",
            "password" => Hash::make('secret')
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'test',
            'email' => 'test@mail.com'
        ]);

        $credentials = [
            "username" => "test",
            "password" => "secret"
        ];

        $response = $this->post(route('login'), $credentials);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        putenv("TEST_UNIT=false");
    }

    /**
     * Verifica que las credenciales de acceso sean inválidas
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function testinvalidCredentials()
    {
        $credentials = [
            "username" => "test",
            "password" => "secret"
        ];

        $this->assertInvalidCredentials($credentials);
    }

    /**
     * Verifica que el campo username sea obligatorio
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function testUsernameRequired()
    {
        $credentials = [
            "username" => null,
            "password" => "secret"
        ];

        $response = $this->from('/login')->post('/login', $credentials);
        $response->assertRedirect('/login')->assertSessionHasErrors([
            'username' => 'El nombre de usuario es obligatorio.',
        ]);
    }

    /**
     * Verifica que el usuario existe en base de datos
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function testUsernameExists()
    {
        $credentials = [
            "username" => 'testUser', //Usuario que no existe en base de datos
            "password" => "secret"
        ];

        $response = $this->from('/login')->post('/login', $credentials);
        $response->assertRedirect('/login')->assertSessionHasErrors([
            'username' => 'Estas credenciales no coinciden con nuestros registros',
        ]);
    }

    /**
     * Verifica que el campo de contraseña sea obligatorio
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function testPasswordRequired()
    {
        $credentials = [
            "username" => "test",
            "password" => null
        ];

        $response = $this->from('/login')->post('/login', $credentials);
        $response->assertRedirect('/login')->assertSessionHasErrors([
            'password' => 'La contraseña es obligatoria.',
        ]);
    }
}
