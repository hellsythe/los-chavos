<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Service;
use App\Models\Subservice;
use App\Models\Garment;
use App\Models\Setting;
use App\Models\Typography;
use Illuminate\Console\Command;
use Sdkconsultoria\Core\Models\Role;
use Spatie\Permission\Models\Permission;

class Start extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Llena las configuraciones basicas';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->createPermissions();
        $this->createServices();
        $this->createGarment();
        $this->createTypography();
        $this->createRoles();
        $this->deleteRoles();
        $this->assingPermissionsToVenta();
        $this->assingPermissionsToBordador();
        $this->assingPermissionsToEstampador();
        $this->setDefaultConfig();
        $this->createBranch();

        $this->info('Configuraciones basicas creadas correctamente');
    }

    private function createServices()
    {

        $bordado = Service::where('name', 'Bordado')->first();
        if (!$bordado) {
            $bordado = new Service();
            $bordado->name = 'Bordado';
            $bordado->status = Service::STATUS_ACTIVE;
            $bordado->save();
        }

        $this->createSubservice($bordado);

        $estampado = Service::where('name', 'Estampado')->first();
        if (!$estampado) {
            $estampado = new Service();
            $estampado->name = 'Estampado';
            $estampado->status = Service::STATUS_ACTIVE;
            $estampado->save();
        }

        $ponchado_nuevo = Subservice::where('name', 'Estampado')->first();
        if (!$ponchado_nuevo) {
            $ponchado_nuevo = new Subservice();
            $ponchado_nuevo->name = 'Estampado';
            $ponchado_nuevo->service_id = $estampado->id;
            $ponchado_nuevo->status = Subservice::STATUS_ACTIVE;
            $ponchado_nuevo->save();
        }
    }

    private function createSubservice($service)
    {
        $ponchado_existente = Subservice::where('name', 'Ponchado existente')->first();
        if (!$ponchado_existente) {
            $ponchado_existente = new Subservice();
            $ponchado_existente->name = 'Ponchado existente';
            $ponchado_existente->service_id = $service->id;
            $ponchado_existente->status = Subservice::STATUS_ACTIVE;
            $ponchado_existente->save();
        }

        $personalizado = Subservice::where('name', 'Personalizado')->first();
        if (!$personalizado) {
            $personalizado = new Subservice();
            $personalizado->name = 'Personalizado';
            $personalizado->service_id = $service->id;
            $personalizado->status = Subservice::STATUS_ACTIVE;
            $personalizado->save();
        }

        $ponchado_modificado = Subservice::where('name', 'Ponchado modificado')->first();
        if (!$ponchado_modificado) {
            $ponchado_modificado = new Subservice();
            $ponchado_modificado->name = 'Ponchado modificado';
            $ponchado_modificado->service_id = $service->id;
            $ponchado_modificado->status = Subservice::STATUS_ACTIVE;
            $ponchado_modificado->save();
        }

        $ponchado_nuevo = Subservice::where('name', 'Ponchado nuevo')->first();
        if (!$ponchado_nuevo) {
            $ponchado_nuevo = new Subservice();
            $ponchado_nuevo->name = 'Ponchado nuevo';
            $ponchado_nuevo->service_id = $service->id;
            $ponchado_nuevo->status = Subservice::STATUS_ACTIVE;
            $ponchado_nuevo->save();
        }
    }

    private function createGarment()
    {
        $this->createGarmentModel('Playera');
        $this->createGarmentModel('Camisa');
        $this->createGarmentModel('Gorra/cachucha');
        $this->createGarmentModel('Tela/Parche');
        $this->createGarmentModel('Camisa Manga Larga');
        $this->createGarmentModel('Chaleco');
        $this->createGarmentModel('Pantalón');
        $this->createGarmentModel('Playera Polo');
        $this->createGarmentModel('Sudadera Hombre');
        $this->createGarmentModel('Short');
        $this->createGarmentModel('Traje de baño 1 pieza');
        $this->createGarmentModel('Falda');

    }

    private function createGarmentModel(string $name)
    {
        $model = Garment::where('name', $name)->first();
        if (!$model) {
            $model = new Garment();
            $model->name = $name;
            $model->status = Garment::STATUS_ACTIVE;
            $model->save();
        }

        $model->preview = url('/').'/storage/garment/'.$model->id.'.jpg';
        $model->save();
    }

    private function createTypography()
    {
        $models = [
            'Letra Barbacoa',
            'Letra Model',
            'Letra Platano frito',
            'Letra Chilaquiles',
            'Letra Volovan',
            'Letra Carne Asada',
            'Letra Taquito',
            'Letra Pico de Gallo',
            'Letra Albóndiga',
            'Letra Pozole',
            'Letra Al Pastor',
            'Letra Chanfaina',
            'Letra Carnitas',
            'Letra Mojarra',
            'Letra Garnacha',
            'Letra Picada',
            'Letra Empanada',
            'Letra Mollete',
            'Letra Tlayuda',
            'Letra Guacamole',
            'Letra Enfrijolada',
            'Letra Mondongo',
        ];

        foreach ($models as $model) {
            $this->createModelByName(Typography::class, $model);
        }
    }

    private function createModelByName($class, $name)
    {
        $model = $class::where('name', $name)->first();
        if (!$model) {
            $model = new $class();
            $model->name = $name;
            $model->status = $class::STATUS_ACTIVE;
            $model->save();
        }
    }

    private function deleteRoles()
    {
        Role::where('name', 'user')->delete();
        Role::where('name', 'admin')->delete();
    }

    private function createRoles()
    {
        $role = Role::firstOrCreate(['name' => 'Punto de venta', 'status' => Role::STATUS_ACTIVE]);
        $role = Role::firstOrCreate(['name' => 'Bordador', 'status' => Role::STATUS_ACTIVE]);
    }

    private function createPermissions()
    {
        $permission = Permission::firstOrCreate(['name' => 'cash_box_report:viewAny']);
        $permission = Permission::firstOrCreate(['name' => 'cash_box_report:view']);
    }

    private function assingPermissionsToVenta()
    {
        $role = Role::firstOrCreate(['name' => 'Punto de venta', 'status' => Role::STATUS_ACTIVE]);
        $role->givePermissionTo('payment:create');
        $role->givePermissionTo('order:create');
        $role->givePermissionTo('order:view');
        $role->givePermissionTo('order:viewAny');
        $role->givePermissionTo('design:viewAny');
        $role->givePermissionTo('design:view');
        $role->givePermissionTo('typography:viewAny');
        $role->givePermissionTo('typography:view');
        $role->givePermissionTo('service:view');
        $role->givePermissionTo('service:viewAny');
        $role->givePermissionTo('subservice:view');
        $role->givePermissionTo('subservice:viewAny');
        $role->givePermissionTo('garment:view');
        $role->givePermissionTo('garment:viewAny');
        $role->givePermissionTo('order_detail:viewAny');
        $role->givePermissionTo('order_detail:view');
        $role->givePermissionTo('design_print:viewAny');
        $role->givePermissionTo('design_print:view');
        $role->givePermissionTo('cash_box_report:viewAny');
        $role->givePermissionTo('cash_box_report:view');
        $role->givePermissionTo('client:viewAny');
        $role->givePermissionTo('client:view');
    }

    private function assingPermissionsToBordador()
    {
        $role = Role::firstOrCreate(['name' => 'Bordador', 'status' => Role::STATUS_ACTIVE]);
        $this->addDefaultPermissions($role);
    }

    private function assingPermissionsToEstampador()
    {
        $role = Role::firstOrCreate(['name' => 'Estampador', 'status' => Role::STATUS_ACTIVE]);
        $this->addDefaultPermissions($role);
    }

    private function addDefaultPermissions($role)
    {
        $role->givePermissionTo('order:view');
        $role->givePermissionTo('order:viewAny');
        $role->givePermissionTo('design:viewAny');
        $role->givePermissionTo('design:view');
        $role->givePermissionTo('typography:viewAny');
        $role->givePermissionTo('typography:view');
        $role->givePermissionTo('service:view');
        $role->givePermissionTo('service:viewAny');
        $role->givePermissionTo('subservice:view');
        $role->givePermissionTo('subservice:viewAny');
        $role->givePermissionTo('garment:view');
        $role->givePermissionTo('garment:viewAny');
        $role->givePermissionTo('order_detail:viewAny');
        $role->givePermissionTo('order_detail:view');
        $role->givePermissionTo('design_print:viewAny');
        $role->givePermissionTo('design_print:view');
        $role->givePermissionTo('client:viewAny');
        $role->givePermissionTo('client:view');
    }

    private function setDefaultConfig()
    {
        $model = Setting::where('name', 'new_embroidery_price')->first();

        if (!$model) {
            $model = new Setting();
            $model->name = 'new_embroidery_price';
            $model->label = 'Costo por nuevo bordado';
            $model->value = '200';
            $model->status = Setting::STATUS_ACTIVE;
            $model->save();
        }

        $model = Setting::where('name', 'update_embroidery_price')->first();

        if(!$model)
        {
            $model = new Setting();
            $model->name = 'update_embroidery_price';
            $model->label = 'Costo por actualizar bordado';
            $model->value = '45';
            $model->status = Setting::STATUS_ACTIVE;
            $model->save();
        }
    }

    private function createBranch()
    {
        $model = Branch::where('name', 'Tierra blanca')->first();
        if (!$model) {
            $model = new Branch();
            $model->name = 'Tierra blanca';
            $model->address = 'Calle 1';
            $model->status = Branch::STATUS_ACTIVE;
            $model->save();
        }

        $model = Branch::where('name', 'Tres valles')->first();
        if (!$model) {
            $model = new Branch();
            $model->name = 'Tres valles';
            $model->address = 'Calle 1';
            $model->status = Branch::STATUS_ACTIVE;
            $model->save();
        }
    }
}
