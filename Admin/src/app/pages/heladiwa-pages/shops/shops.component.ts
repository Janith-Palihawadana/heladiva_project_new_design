import { Component } from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {NgbModal, NgbModalRef} from "@ng-bootstrap/ng-bootstrap";
import {GlobalService} from "../../../core/services/global.service";
import Swal from "sweetalert2";
import {HeladivaPageService} from "../heladiva-page.service";
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-shops',
  templateUrl: './shops.component.html',
  styleUrls: ['./shops.component.scss']
})
export class ShopsComponent {

  filterForm!: FormGroup;
  totalRecords: number = 0;
  page = 1;
  pageSize = 10;
  tableData: any;
  modelRef!: NgbModalRef;
  isEdit: boolean = false;
  addForm!: FormGroup;
  submitted = false;
  Agencies: any ;
  currentAgency: number = 1;
  agency_id: any;
  route_list: any;

  constructor(
    private modalService: NgbModal,
    private formBuilder: FormBuilder,
    private HeladivaPagesService :HeladivaPageService,
    private globalService : GlobalService,
    private spinner: NgxSpinnerService
  ) {

    this.agency_id = localStorage.getItem('agency_id');
    this.filterForm = this.formBuilder.group({
      keyword: [null],
      is_active:[true],
    });

    this.addForm = this.formBuilder.group({
      shop_name: ['',[Validators.required]],
      shop_ref: [null],
      is_active: [true,[Validators.required]],
      due_date_count: [null,[Validators.required, Validators.pattern('^[0-9]*$')]],
      route_id: [null,[Validators.required]]
    });
  }

  ngOnInit(): void {
    this._fetchData();
  }

  filterReset() {
    this.filterForm.reset();
    this.filterForm.patchValue({
      is_active:true,
    })
    this._fetchData();
  }

  _fetchData() {
    this.spinner.show();
    this.HeladivaPagesService.getShopLists(this.filterForm.value, 'shops/get-shop_list?page_no=' + this.page + '&page_size=' + this.pageSize).subscribe({
      next: (response: any) => {
        this.tableData = response.data.shop_list;
        this.totalRecords = response.data.total_count;
        this.route_list = response.data.route_list;
        this.spinner.hide();
      },
      error: (error: any) => {
        this.spinner.hide();
        this.globalService.showError(error.message || 'Something went wrong');
      }
    });
  }

  async openModal(modal: any) {
    this.modelRef = this.modalService.open(modal, {size: 'md', centered: true, keyboard: false, backdrop: 'static'});
  }

  closeModel() {
    this.modelRef.close();
    this.addForm.reset();
    this.addForm.patchValue({
      is_active:true
    });
  }

  onSubmit() {
    this.submitted = true;
    if (this.addForm.invalid) {
      return;
    }
    else {
      this.spinner.show();
      const functionType = this.addForm.value.shop_ref ? 'updateShop' : 'saveShop';
      const url = this.addForm.value.shop_ref ? 'shops/edit-shop' : 'shops/save-shop';
      this.HeladivaPagesService[functionType](this.addForm.value, url).subscribe({
        next: (response: any) => {
          this.globalService.showSuccess(response.message);
          this.modelRef.close();
          this.addForm.reset();
          this.addForm.patchValue({
            is_active:true
          });
          this._fetchData();
          this.spinner.hide();
        },
        error: (error: any) => {
          this.spinner.hide();
          this.globalService.showError(error.message || 'Something went wrong');
        }
      });
    }
  }

  async editRow(modal: any, row: any) {
    this.isEdit = true;
    this.submitted = false;
    this.addForm.reset();

    this.HeladivaPagesService.getShopDetails('shops/get-shop-details?ref=' + row.shop_ref).subscribe({
      next: (response: any) => {
        this.modelRef = this.modalService.open(modal, {size: 'md', centered: true, keyboard: false, backdrop: 'static'});
        this.addForm.patchValue(
          {
            shop_ref: row.shop_ref,
            shop_name: response.data.shopDetails.shop_name,
            is_active: response.data.shopDetails.is_active,
            due_date_count: response.data.shopDetails.due_date_count,
            route_id: response.data.routes
          });
      },
      error: (error: any) => {
        this.globalService.showError(error.message || 'Something went wrong');
      }
    });
  }

  deleteRow(row: any) {
    Swal.fire(<any>{
      title: 'Delete?',
      text: 'Do you want to delete this record?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#696969',
      confirmButtonText: 'Yes Delete'
    }).then((result) => {
      if (result.value) {
        this.HeladivaPagesService.deleteShop(row.shop_ref, 'shops/delete-shop').subscribe({
          next: (response: any) => {
            this.globalService.showSuccess(response.message);
            this._fetchData();
          },
          error: (error: any) => {
            this.globalService.showError(error.message);
          }
        });
      }
    });
  }


  onPageChange = (pageNumber: number) => {
    this.page = pageNumber;
    this._fetchData();
  }

  get f() {
    return this.addForm.controls;
  }
}

