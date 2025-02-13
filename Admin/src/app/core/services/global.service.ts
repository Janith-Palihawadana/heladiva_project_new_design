import { Injectable } from '@angular/core';
import {HttpClient, HttpHeaders} from "@angular/common/http";
import {Observable} from "rxjs";
import Swal from "sweetalert2";
import {Router} from "@angular/router";
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {ToastrService} from "ngx-toastr";

@Injectable({
  providedIn: 'root'
})
export class GlobalService {

  apiURL: string = 'http://127.0.0.1:8000/api/';
  constructor(private toaster: ToastrService,
              private http: HttpClient,
              private router: Router,
              private modalService: NgbModal,) { }

  getAPIUrl() {
    return this.apiURL;
  }

  getHttpOptions() {
    return {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        Authorization: 'Bearer ' + localStorage.getItem('authToken'),
      })
    };
  }

  getHttpOptionsAuthWithOutContentType() {
    return {
      headers: new HttpHeaders({
        Authorization: 'Bearer ' + localStorage.getItem('authToken')
      })
    };
  }
  showSuccess(message: any = 'Success') {
    this.toaster.success(message, '');
  }

  showInfo(message: any = 'Information',) {
    this.toaster.info(message, '');
  }

  showWarning(message: any = 'Warning',) {
    this.toaster.warning(message, '');
  }

  showNavigationError(message: any = 'Error during navigation',) {
    this.toaster.error(message, '');
  }

  showBackendErrors(messages: any[]) {
    messages.forEach((value) => {
      this.toaster.error(value, '');
    });
  }

  showError(messages: any) {
    if (Array.isArray(messages)) {
      messages.forEach((value) => {
        this.toaster.error(value, '');
      });
    } else {
      this.toaster.error(messages, '');
    }
  }

  // get config file
  getConfig(): Observable<any> {
    return this.http.get('assets/config.json');
  }
  // Calling before app initialize
  loadConfig(): Promise<void> {
    return new Promise<void>((resolve, reject) => {
      this.getConfig().subscribe({
          next: (response: any) => {
            this.apiURL =  response.apiUrl;
            resolve();
          },
          error: (error: any) => {
            reject(error);
          }
        }
      );
    });
  }

  logout() {
    const currentUrl = this.router.url;
    localStorage.removeItem('authToken');
    this.modalService.dismissAll();
    Swal.close();
    this.router.navigate(['/login'], {queryParams: {returnUrl: currentUrl}}).catch((error) => {
      this.showNavigationError();
    });
  }
}
