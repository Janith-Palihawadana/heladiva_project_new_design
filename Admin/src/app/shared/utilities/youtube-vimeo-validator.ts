import { AbstractControl, ValidatorFn, ValidationErrors } from '@angular/forms';

export function youtubeVimeoValidator(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    if (control.value) {
      const youtubeRegex = /^(?:https?:)?(?:\/\/)?(?:youtu\.be\/|(?:www\.|m\.)?youtube\.com\/(?:watch|v|embed)(?:\.php)?(?:\?.*v=|\/))([a-zA-Z0-9\_-]{7,15})(?:[\?&][a-zA-Z0-9\_-]+=[a-zA-Z0-9\_-]+)*$/;
      const vimeoRegex = /(?:http|https)?:?\/?\/?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|video\/|)(\d+)(?:|\/\?)/;

      if (!(youtubeRegex.test(control.value) || vimeoRegex.test(control.value))) {
        return { invalidUrl: true };
      }
    }

    return null;
  };
}
