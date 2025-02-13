import { Component } from '@angular/core';
import {NgxPermissionsService} from "ngx-permissions";
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import Swal from "sweetalert2";

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {
  title = 'Heladiva';


  constructor(private modalService: NgbModal,
              private permissionsService: NgxPermissionsService) {
    window.addEventListener('popstate', () => {
      this.modalService.dismissAll();
      Swal.close();
    });

    // load permissions
    const userPermissions = localStorage.getItem('user_permissions');
    this.permissionsService.loadPermissions(userPermissions ? [userPermissions] : []);
  }

}


