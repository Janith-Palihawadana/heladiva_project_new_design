import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {

  constructor() { }

  isAuthenticated(): boolean {
    const authToken = localStorage.getItem('authToken');
    if (authToken) {
      // return this.isTokenValid(authToken);
      return true;
    }
    return false;
  }

  private isTokenValid(authToken: string): boolean {
    try {
      // Decode the token to get its payload (assuming it's a JWT)
      const tokenPayload = JSON.parse(atob(authToken.split('.')[1]));

      // Check if the token has not expired
      const tokenExpiration = tokenPayload.exp;
      const currentTimeInSeconds = Math.floor(Date.now() / 1000);

      return tokenExpiration >= currentTimeInSeconds;
    } catch (error) {
      // If there's an error decoding the token or any other issue, consider it invalid
      return false;
    }
  }
}
