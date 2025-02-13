import { Injectable } from '@angular/core';
import {Router} from "@angular/router";
import {GlobalService} from "../core/services/global.service";
import {HttpClient} from "@angular/common/http";

@Injectable({
  providedIn: 'root'
})
export class AccountService {

  constructor(private http: HttpClient,
              private globalService: GlobalService,
              private router: Router) { }

  login(data: any) {
    return this.http.post(this.globalService.getAPIUrl() + 'auth/login', data , this.globalService.getHttpOptions());
  }

}
