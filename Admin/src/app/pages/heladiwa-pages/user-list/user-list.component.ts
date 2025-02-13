import { Component } from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {NgbModal, NgbModalRef} from "@ng-bootstrap/ng-bootstrap";
import {GlobalService} from "../../../core/services/global.service";
import Swal from "sweetalert2";
import {HeladivaPageService} from "../heladiva-page.service";
import {passwordMatchValidator} from "../../../shared/utilities/validators";
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-user-list',
  templateUrl: './user-list.component.html',
  styleUrls: ['./user-list.component.scss']
})
export class UserListComponent {

  addForm!: FormGroup;
  filterForm!: FormGroup;
  modelRef!: NgbModalRef;
  totalRecords: number = 0;
  page = 1;
  pageSize = 10;
  isEdit = false;
  submitted = false;
  tableData: any ;
  userRoles: any ;
  fieldTextType!: boolean;
  agency_id: any;


  constructor(
    private modalService: NgbModal,
    private formBuilder: FormBuilder,
    private HeladivaPagesService :HeladivaPageService,
    private globalService : GlobalService,
    private spinner: NgxSpinnerService
  ) {

    this.agency_id = localStorage.getItem('agency_id');
    this.addForm = this.formBuilder.group({
      user_ref: [null],
      full_name: ['', [Validators.required]],
      email: ['', [Validators.required, Validators.email]],
      role_id: ['', [Validators.required]],
      is_active:[true],
      agency_id: this.agency_id,
      phone_number: [null, [Validators.required, Validators.pattern('^\\+94[0-9]{9}$'), Validators.minLength(11)]],
      password: [null, [Validators.pattern('^(?=.*[A-Za-z])(?=.*\\d)(?=.*[@$!%*#?&])[A-Za-z\\d@$!%*#?&]{8,}$')]],
      confirm_password: [null],
    },    {
      validators: passwordMatchValidator(),
    });

    this.filterForm = this.formBuilder.group({
      keyword: [null],
      is_active:[true],
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

  filterReset() {
    this.filterForm.reset();
    this._fetchData();
  }

  _fetchData() {
    this.spinner.show();
    this.HeladivaPagesService.getUsers(this.filterForm.value, 'users/get-users?page_no=' + this.page + '&page_size=' + this.pageSize).subscribe({
      next: (response: any) => {
        this.tableData = response.data.users;
        this.userRoles = response.data.user_roles;
        this.totalRecords = response.data.total_count;
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
    this.spinner.show();
    this.isEdit = true;
    this.submitted = false;
    this.addForm.reset();
    this.modelRef = this.modalService.open(modal, {size: 'md', centered: true, keyboard: false, backdrop: 'static'});
    this.addForm.patchValue(
      {
        user_ref: row.user_ref,
        full_name:row.full_name,
        email:row.email,
        phone_number:row.phone_number,
        role_id:row.role_id,
        is_active:row.is_active
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
      const functionType = this.addForm.value.user_ref ? 'updateUser' : 'saveUser';
      const url = this.addForm.value.user_ref ? 'users/update-user' : 'users/save-user';

      console.log(this.addForm.value.user_ref);
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
        this.HeladivaPagesService.deleteUser(row.user_ref, 'users/delete-user').subscribe({
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

  toggleFieldTextType() {
    this.fieldTextType = !this.fieldTextType;
  }

  closeModel() {
    this.addForm.reset();
    this.addForm.patchValue({
      is_active:true
    });
  }
}
