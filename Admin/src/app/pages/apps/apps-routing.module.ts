import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { CalendarComponent } from './calendar/calendar.component';
import { ChatComponent } from './chat/chat.component';
import { InboxComponent } from './inbox/inbox.component';
import { ReademailComponent } from './reademail/reademail.component';
import { InvoiceListComponent } from './invoice-list/invoice-list.component';
import { InvoiceDetailComponent } from './invoice-detail/invoice-detail.component';
import { UserGridComponent } from './user-grid/user-grid.component';
import { UserListComponent } from './user-list/user-list.component';
import { UserProfileComponent } from './user-profile/user-profile.component';

const routes: Routes = [
  {
    path: 'calender',
    component: CalendarComponent
  },
  {
    path: 'chat',
    component: ChatComponent
  },
  {
    path: 'inbox',
    component: InboxComponent
  },
  {
    path: 'read/:id',
    component: ReademailComponent
  },
  {
    path: 'invoice-list',
    component: InvoiceListComponent
  },
  {
    path: 'invoice-detail',
    component: InvoiceDetailComponent
  },
  {
    path: 'user-grid',
    component: UserGridComponent
  },
  {
    path: 'user-list',
    component: UserListComponent
  },
  {
    path: 'profile',
    component: UserProfileComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AppsRoutingModule { }
