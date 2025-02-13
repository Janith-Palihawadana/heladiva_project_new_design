import {HttpEvent, HttpHandler, HttpInterceptor, HttpRequest} from '@angular/common/http';
import {NgxSpinnerService} from "ngx-spinner";
import {Router} from "@angular/router";
import {Injectable} from "@angular/core";
import {Observable} from "rxjs";
import {finalize} from "rxjs/operators";

@Injectable()
export class LoadingInterceptor implements HttpInterceptor {

  private excludedRoutes = [
    '/login',
  ];

  constructor(private spinner: NgxSpinnerService,
              private router: Router) {}

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {

    const currentRoute = this.router.url.split('?')[0];
    const isExcluded = this.excludedRoutes.includes(currentRoute);

    if (isExcluded) {
      return next.handle(request);
    }
    this.spinner.show();
    return next.handle(request).pipe(
      finalize(() => {
        setTimeout(() => {
          this.spinner.hide();
        }, 500);
      })
    );
  }
}
