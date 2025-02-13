import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {UserRolesComponent} from "./user-roles/user-roles.component";
import {UserListComponent} from "./user-list/user-list.component";
import {UploadInvoicesComponent} from "./upload-invoices/upload-invoices.component";
import {SaleRefComponent} from "./sele-ref/sale-ref.component";
import {VehicleComponent} from "./vehicle/vehicle.component";
import {RoutesComponent} from "./routes/routes.component";
import {AreasComponent} from "./areas/areas.component";
import {ShopsComponent} from "./shops/shops.component";
import {SharedModule} from "../../shared/shared.module";
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {
  NgbDropdownModule,
  NgbModule,
  NgbNavModule,
  NgbPaginationModule,
  NgbTooltipModule
} from "@ng-bootstrap/ng-bootstrap";
import {WidgetModule} from "../../shared/widget/widget.module";
import {CountToModule} from "angular-count-to";
import {NgApexchartsModule} from "ng-apexcharts";
import {PagesRoutingModule} from "../pages-routing.module";
import {SimplebarAngularModule} from "simplebar-angular";
import {CarouselModule} from "ngx-owl-carousel-o";
import {FeatherModule} from "angular-feather";
import {allIcons} from "angular-feather/icons";
import {RouterModule} from "@angular/router";
import {AppsModule} from "../apps/apps.module";
import {ExtraspagesModule} from "../extraspages/extraspages.module";
import {ComponentsModule} from "../components/components.module";
import {ExtendedModule} from "../extended/extended.module";
import {LightboxModule} from "ngx-lightbox";
import {FormModule} from "../form/form.module";
import {TablesModule} from "../tables/tables.module";
import {ChartModule} from "../chart/chart.module";
import {NgSelectModule} from "@ng-select/ng-select";
import { DashViewComponent } from './dash-view/dash-view.component';
import {HeladivaPagesRoutingModule} from "./heladiva-pages-routing.module";
import {LeafletModule} from "@asymmetrik/ngx-leaflet";



@NgModule({
  declarations: [
    UserRolesComponent,
    UserListComponent,
    AreasComponent,
    ShopsComponent,
    RoutesComponent,
    VehicleComponent,
    SaleRefComponent,
    UploadInvoicesComponent,
    DashViewComponent
  ],
  imports: [
    CommonModule,
    HeladivaPagesRoutingModule,
    CountToModule,
    NgApexchartsModule,
    CarouselModule,
    SimplebarAngularModule,
    WidgetModule,
    NgbModule,
    FormsModule,
    ReactiveFormsModule,
    NgSelectModule,
    SharedModule,
    LeafletModule
  ]
})
export class HeladivaPagesModule { }
