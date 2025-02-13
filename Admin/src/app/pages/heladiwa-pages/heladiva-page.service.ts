import { Injectable } from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {GlobalService} from "../../core/services/global.service";

@Injectable({
  providedIn: 'root'
})
export class HeladivaPageService {

  constructor(private http: HttpClient,
              private globalService: GlobalService) { }

  saveRole(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  updateRole(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  getRolePermission(url: string, data: any) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  getData(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  deleteUserRole(data:any, url: string) {
    return this.http.delete(this.globalService.getAPIUrl() + url + '?role_ref=' + data, this.globalService.getHttpOptions());
  }

  saveUser(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  updateUser(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  getUsers(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  deleteUser(data:any, url: string) {
    return this.http.delete(this.globalService.getAPIUrl() + url + '?user_ref=' + data, this.globalService.getHttpOptions());
  }

  getAgencyDetails(url: string) {
    return this.http.get(this.globalService.getAPIUrl() + url , this.globalService.getHttpOptions());
  }

  getAreaDetails(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  deleteAreas(data:any, url: string) {
    return this.http.delete(this.globalService.getAPIUrl() + url + '?shop_ref=' + data, this.globalService.getHttpOptions());
  }

  saveAreas(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  updateArea(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  getShopLists(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  saveShop(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  updateShop(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  deleteShop(data:any, url: string) {
    return this.http.delete(this.globalService.getAPIUrl() + url + '?shop_ref=' + data, this.globalService.getHttpOptions());
  }

  getRouteLists(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  saveRoute(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  updateRoute(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  deleteRoute(data:any, url: string) {
    return this.http.delete(this.globalService.getAPIUrl() + url + '?ref=' + data, this.globalService.getHttpOptions());
  }

  getRouteDetails(url:string){
    return this.http.get(this.globalService.getAPIUrl() + url ,this.globalService.getHttpOptions());
  }

  getShopDetails(url:string){
    return this.http.get(this.globalService.getAPIUrl() + url ,this.globalService.getHttpOptions());
  }

  saveVehicle(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  updateVehicle(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  deleteVehicle(data:any, url: string){
    return this.http.delete(this.globalService.getAPIUrl() + url + '?ref=' + data, this.globalService.getHttpOptions());
  }

  saveSaleRef(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  updateSaleRef(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  deleteSaleRef(data:any, url: string){
    return this.http.delete(this.globalService.getAPIUrl() + url + '?ref=' + data, this.globalService.getHttpOptions());
  }

  saveInvoice(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptionsAuthWithOutContentType());
  }

  editInvoice(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  getInvoiceList(data:any,url: string) {
    return this.http.post(this.globalService.getAPIUrl() + url , data, this.globalService.getHttpOptions());
  }

  deleteInvoice(data:any, url: string){
    return this.http.delete(this.globalService.getAPIUrl() + url + '?ref=' + data, this.globalService.getHttpOptions());
  }
}
