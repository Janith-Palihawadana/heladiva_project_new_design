import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { AuthenticationService } from '../services/authentication.service';
import {GlobalService} from "../services/global.service";

@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {
    constructor(
        private router: Router,
        private authService : AuthenticationService,
        private globalService: GlobalService,
    ) { }

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    if (this.authService.isAuthenticated()) {
      return true;
    }
    // Token is not present or invalid, redirect to login page
    this.router.navigate(['/login'], {
      queryParams: {returnUrl: state.url},
    }).catch(() => {
      this.globalService.showNavigationError();
    });
    // this.globalService.showError('You must be logged in to access this page');
    return false;
  }
}
