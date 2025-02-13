import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { NgxYoutubePlayerModule } from 'ngx-youtube-player';
import { NgbRatingModule, NgbToastModule } from '@ng-bootstrap/ng-bootstrap';
import { NgxSliderModule } from '@angular-slider/ngx-slider';

import { SharedModule } from '../../shared/shared.module';
import { ExtendedRoutingModule } from './extended-routing.module';
import { LightboxComponent } from './lightbox/lightbox.component';
import { SweetalertComponent } from './sweetalert/sweetalert.component';
import { RatingComponent } from './rating/rating.component';
import { RangesliderComponent } from './rangeslider/rangeslider.component';
import { NotificationsComponent } from './notifications/notifications.component';
import { ToastsContainer } from './notifications/toasts-container.component';

@NgModule({
  declarations: [
    LightboxComponent,
    SweetalertComponent,
    RatingComponent,
    RangesliderComponent,
    NotificationsComponent,
    ToastsContainer
  ],
  imports: [
    CommonModule,
    SharedModule,
    NgxYoutubePlayerModule,
    NgbRatingModule,
    NgbToastModule,
    ExtendedRoutingModule,
    NgxSliderModule
  ]
})
export class ExtendedModule { }
