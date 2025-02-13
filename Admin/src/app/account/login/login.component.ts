import { Component, OnInit } from '@angular/core';
import {FormBuilder ,FormGroup, Validators} from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { LAYOUT_MODE } from '../../layouts/layouts.model';
import { GlobalService } from "../../core/services/global.service";
import { AccountService } from "../account.service";
import {NgxPermissionsService} from "ngx-permissions";


@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})

export class LoginComponent implements OnInit {

  year: number = new Date().getFullYear();
  loginForm!: FormGroup;
  returnUrl!: string;
  layout_mode!: string;
  fieldTextType!: boolean;
  submitted = false;
  spinner: boolean = false;

  constructor(private formBuilder: FormBuilder,
              private route: ActivatedRoute,
              private router: Router,
              private globalService: GlobalService,
              private userService: AccountService,
              private permissionsService: NgxPermissionsService,
  ) {
    this.loginForm = this.formBuilder.group({
      user: [null, [Validators.required, Validators.email]],
      password: [null, [Validators.required]],
    });
  }

  ngOnInit(): void {
    this.layout_mode = LAYOUT_MODE
    if (this.layout_mode === 'dark') {
      document.body.setAttribute("data-layout-mode", "dark");
    }
    // get return url from route parameters or default to '/'
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';

    document.body.setAttribute('data-layout', 'vertical');
    localStorage.clear();
  }

  get lf() { return this.loginForm.controls; }

  onSubmit() {
    this.submitted = false;
    this.spinner = true;
    if (this.loginForm.invalid) {
      this.submitted = true;
      this.spinner = false;
      return;
    }
    this.userService.login(this.loginForm.value).subscribe({
      next: (response: any) => {
        localStorage.setItem('authToken', response.data['access_token']);
        localStorage.setItem('userName', response.data['name']);
        localStorage.setItem('agency_name', response.data['agency_name']);
        localStorage.setItem('user_permissions', response.data['user_permissions']);
        localStorage.setItem('agency_id', response.data['agency_id']);

        // load permissions
        const userPermissions = localStorage.getItem('user_permissions');
        this.permissionsService.loadPermissions(userPermissions ? [userPermissions] : []);
        this.router.navigate(['/'])
        this.spinner = false;
      },
      error: (error: any) => {
        this.spinner = false;
        this.globalService.showError(error.message || 'Something went wrong');
      }
    });
  }

  toggleFieldTextType() {
    this.fieldTextType = !this.fieldTextType;
  }
}
