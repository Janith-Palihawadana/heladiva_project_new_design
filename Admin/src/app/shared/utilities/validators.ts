import { AbstractControl, ValidatorFn } from '@angular/forms';

export function passwordMatchValidator(): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } | null => {
    const password = control.get('password')?.value;
    const confirm_password = control.get('confirm_password')?.value;

    if(!password && !confirm_password){
      return null;
    }
    return password === confirm_password ? null : { 'passwordMismatch': true };
  };
}
