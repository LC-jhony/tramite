<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // PERSONAS NATURALES (representation = false)
        Customer::create([
            'representation' => false,
            'first_name' => 'María Elena',
            'last_name' => 'Vargas Condori',
            'dni' => '4821936',
            'phone' => '72581432',
            'email' => 'maria.vargas@example.com',
            'address' => 'Av. Busch N.º 1245, Zona Central',
        ]);

        Customer::create([
            'representation' => false,
            'first_name' => 'Juan Carlos',
            'last_name' => 'Mamani Quispe',
            'dni' => '3917452',
            'phone' => '76149208',
            'email' => 'juan.mamani@example.com',
            'address' => 'Calle Ayacucho N.º 890, Zona San Martín',
        ]);

        Customer::create([
            'representation' => false,
            'first_name' => 'Ana Lucía',
            'last_name' => 'Flores Ticona',
            'dni' => '5276014',
            'phone' => '73450127',
            'email' => 'ana.flores@example.com',
            'address' => 'Av. Periférica N.º 455, Zona Norte',
        ]);

        Customer::create([
            'representation' => false,
            'first_name' => 'Roberto',
            'last_name' => 'Choque Lanca',
            'dni' => '6104873',
            'phone' => '78902315',
            'email' => 'roberto.choque@example.com',
            'address' => 'Calle Mercado N.º 212, Zona Sur',
        ]);

        Customer::create([
            'representation' => false,
            'first_name' => 'Carla Fernanda',
            'last_name' => 'Rojas Apaza',
            'dni' => '7358290',
            'phone' => '70264819',
            'email' => 'carla.rojas@example.com',
            'address' => 'Av. Circunvalación N.º 1780, Zona Oeste',
        ]);

        // PERSONAS JURÍDICAS (representation = true)
        Customer::create([
            'representation' => true,
            'full_name' => 'Luis Fernando Salazar Mendoza',
            'dni' => '4471205',
            'company' => 'Constructora Andina S.R.L.',
            'ruc' => '1023456027',
            'phone' => '46211087',
            'email' => 'contacto@andina.com.bo',
            'address' => 'Av. Arce N.º 2340, Edificio Torre Norte, Piso 8',
        ]);

        Customer::create([
            'representation' => true,
            'full_name' => 'Patricia Rocío Herrera Véliz',
            'dni' => '5620947',
            'company' => 'Servicios Integrales Tupac S.A.',
            'ruc' => '2045678033',
            'phone' => '46439215',
            'email' => 'administracion@tupac.com.bo',
            'address' => 'Calle Loayza N.º 1125, Zona Miraflores',
        ]);

        Customer::create([
            'representation' => true,
            'full_name' => 'Óscar Alfonso Paredes Ríos',
            'dni' => '3185742',
            'company' => 'Distribuidora Altiplano Ltda.',
            'ruc' => '3012345019',
            'phone' => '46670348',
            'email' => 'gerencia@altiplano.com.bo',
            'address' => 'Av. Juan de la Ruda N.º 990, Zona Industrial',
        ]);

        Customer::create([
            'representation' => true,
            'full_name' => 'Gabriela Soledad Navarro Peña',
            'dni' => '6903518',
            'company' => 'Consultora Risankawa E.I.R.L.',
            'ruc' => '4078912046',
            'phone' => '46182750',
            'email' => 'info@risankawa.com.bo',
            'address' => 'Calle Lanza N.º 675, Zona Calacoto',
        ]);

        Customer::create([
            'representation' => true,
            'full_name' => 'Diego Armando Cruz Villegas',
            'dni' => '5047361',
            'company' => 'Transportes Callejón S.A.',
            'ruc' => '1567893024',
            'phone' => '46590176',
            'email' => 'operaciones@callejon.com.bo',
            'address' => 'Av. Circunvalación N.º 3210, Zona Sud',
        ]);
    }
}
