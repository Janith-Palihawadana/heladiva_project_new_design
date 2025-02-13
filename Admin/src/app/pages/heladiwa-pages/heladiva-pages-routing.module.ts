import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import {AreasComponent} from "./areas/areas.component";
import {UserRolesComponent} from "./user-roles/user-roles.component";
import {UserListComponent} from "./user-list/user-list.component";
import {ShopsComponent} from "./shops/shops.component";
import {SaleRefComponent} from "./sele-ref/sale-ref.component";
import {RoutesComponent} from "./routes/routes.component";
import {VehicleComponent} from "./vehicle/vehicle.component";
import {UploadInvoicesComponent} from "./upload-invoices/upload-invoices.component";
import {DashViewComponent} from "./dash-view/dash-view.component";

const routes: Routes = [
  {
    path: '',
    component: DashViewComponent
  },
  {
    path: 'user-roles',
    component: UserRolesComponent
  },
  {
    path: 'areas',
    component: AreasComponent
  },
  {
    path: 'user-list',
    component: UserListComponent
  },
  {
    path: 'shops',
    component: ShopsComponent
  },
  {
    path: 'routes',
    component: RoutesComponent
  },
  {
    path: 'sale-ref',
    component: SaleRefComponent
  },
  {
    path: 'vehicle',
    component: VehicleComponent
  },
  {
    path: 'invoice',
    component: UploadInvoicesComponent
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class HeladivaPagesRoutingModule { }
