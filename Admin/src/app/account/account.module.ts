import { NgModule } from '@angular/core';
import { NgbCarouselModule } from '@ng-bootstrap/ng-bootstrap';

import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { AccountRoutingModule } from './account-routing.module';
import { AuthModule } from './auth/auth.module';

import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import {PagesSliderComponent} from "./pages-slider/pages-slider.component";

@NgModule({
  declarations: [
    LoginComponent,
    RegisterComponent,
    PagesSliderComponent
  ],
  imports: [
    CommonModule,
    NgbCarouselModule,
    ReactiveFormsModule,
    FormsModule,
    AccountRoutingModule,
    AuthModule
  ]
})
export class AccountModule { }
