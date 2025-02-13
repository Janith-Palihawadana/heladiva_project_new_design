import { Component } from '@angular/core';
import {FormArray, FormBuilder, FormGroup, Validators} from "@angular/forms";
import {NgbModal, NgbModalRef} from "@ng-bootstrap/ng-bootstrap";
import {GlobalService} from "../../../core/services/global.service";
import Swal from "sweetalert2";
import {HeladivaPageService} from "../heladiva-page.service";
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-user-roles',
  templateUrl: './user-roles.component.html',
  styleUrls: ['./user-roles.component.scss']
})
export class UserRolesComponent {

  addForm!: FormGroup;
  filterForm!: FormGroup;
  modelRef!: NgbModalRef;
  totalRecords: number = 0;
  page = 1;
  pageSize = 10;
  isEdit = false;
  submitted = false;
  tableData: any ;
  agency_id: any;

  constructor(
    private modalService: NgbModal,
    private formBuilder: FormBuilder,
    private HeladivaPagesService : HeladivaPageService,
    private globalService : GlobalService,
    private spinner: NgxSpinnerService
  ) {

    this.agency_id = localStorage.getItem('agency_id');
    this.addForm = this.formBuilder.group({
      role_ref: [null],
      role_name: ['', Validators.required],
      agency_id: this.agency_id,
      permission: this.formBuilder.array([])
    });

    this.filterForm = this.formBuilder.group({
      keyword: [null],
      agency_id: this.agency_id
    });

  }

  ngOnInit(): void {
    this._fetchData();
  }

  get f() {
    return this.addForm.controls;
  }

  async openModal(modal: any) {
    this.modelRef = this.modalService.open(modal, {size: 'md', centered: true, keyboard: false, backdrop: 'static'});
  }

  _fetchData() {
    this.spinner.show();
    this.HeladivaPagesService.getData(this.filterForm.value, 'users/get-user-roles?page_no=' + this.page + '&page_size=' + this.pageSize).subscribe({
      next: (response: any) => {
        this.tableData = response.data.userRole;
        this.totalRecords = response.data.total_count;
        this.addPermissionList(response.data.permission_list);
        this.spinner.hide();
      },
      error: (error: any) => {
        this.spinner.hide();
        this.globalService.showError(error.message || 'Something went wrong');
      }
    });
  }

  onPageChange = (pageNumber: number) => {
    this.page = pageNumber;
    this._fetchData();
  }

  async editRow(modal: any, row: any) {
    this.isEdit = true;
    this.submitted = false;
    this.addForm.reset();
    this.permissionArray.clear();
    this.modelRef = this.modalService.open(modal, {size: 'md', centered: true, keyboard: false, backdrop: 'static'});
    let $data ={
      "role_id" : row.id,
    };
    this.spinner.show();
    this.HeladivaPagesService.getRolePermission('users/get-role-permission',$data).subscribe({
      next: (response: any) => {
        this.addPermissionList(response.data.role_permissions);
      }
    });
    this.addForm.patchValue(
      {
        role_name: row.role_name,
        role_ref:row.role_ref
      }
    );
    this.spinner.hide();
  }

  onSubmit() {
    this.submitted = false;
    if (this.addForm.invalid) {
      this.submitted = true;
      return;
    }
    else {
      this.spinner.show();
      const functionType = this.addForm.value.role_ref ? 'updateRole' : 'saveRole';
      const url = this.addForm.value.role_ref ? 'users/update-user-role' : 'users/save-user-role';

      this.spinner.show();
      this.HeladivaPagesService[functionType](this.addForm.value, url).subscribe({
        next: (response: any) => {
          this.globalService.showSuccess(response.message)
          this.modelRef?.close();
          this._fetchData();
          this.addForm.reset();
          this.spinner.hide();
        },
        error: (error: any) => {
          this.spinner.hide();
          this.globalService.showError(error.message || 'Something went wrong');
        }
      });
    }
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
        this.spinner.show();
        this.HeladivaPagesService.deleteUserRole(row.role_ref, 'users/delete-user-role').subscribe({
          next: (response: any) => {
            this.globalService.showSuccess(response.message);
            this._fetchData();
            this.spinner.hide();
          },
          error: (error: any) => {
            this.spinner.hide();
            this.globalService.showError(error.message);
          }
        });
      }
    });
  }

  get permissionArray(): any {
    return this.addForm.get('permission') as FormArray;
  }

  addPermissionList(fields: any[]) {
    fields.forEach((obj: any, i: number) => {
      this.permissionArray.push(this.addPermission(obj));
    });
  }

  addPermission(data: any) {
    return this.formBuilder.group({
      permission_name: data.permission_name,
      value: data.value === '1',
      permission_id: data.id,
    })
  }

  filterReset() {
    this.filterForm = this.formBuilder.group({
      keyword: [null],
      agency_id: this.agency_id
    });
    this._fetchData();
  }
}
