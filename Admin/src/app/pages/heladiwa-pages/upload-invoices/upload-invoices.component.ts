import { Component } from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {NgbModal, NgbModalRef} from "@ng-bootstrap/ng-bootstrap";
import {GlobalService} from "../../../core/services/global.service";
import Swal from "sweetalert2";
import {HeladivaPageService} from "../heladiva-page.service";
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-upload-invoices',
  templateUrl: './upload-invoices.component.html',
  styleUrls: ['./upload-invoices.component.scss']
})
export class UploadInvoicesComponent {
  filterForm!: FormGroup;
  totalRecords: number = 0;
  page = 1;
  pageSize = 10;
  tableData: any;
  modelRef!: NgbModalRef;
  isEdit: boolean = false;
  addForm!: FormGroup;
  editForm!: FormGroup;
  submitted = false;
  Agencies: any ;
  currentAgency: number = 1;
  agency_id: any;
  route_list: any;
  shop_list: any;
  invoicePreview: any = [];

  constructor(
    private modalService: NgbModal,
    private formBuilder: FormBuilder,
    private HeladivaPagesService :HeladivaPageService,
    private globalService : GlobalService,
    private spinner: NgxSpinnerService
  ) {

    this.filterForm = this.formBuilder.group({
      keyword: [null],
      is_active:[true],
    });

    this.addForm = this.formBuilder.group({
      is_active: [true,[Validators.required]],
      route_id: [null,[Validators.required]]
    });

    this.editForm = this.formBuilder.group({
      shop_id: ['',[Validators.required]],
      invoice_ref: [null],
      invoice_no: ['',[Validators.required]],
      route_id: ['',[Validators.required]],
      amount: ['',[Validators.required]],
      is_active: [true,[Validators.required]],
      date: ['',[Validators.required]],
      remark: [''],
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
    this.HeladivaPagesService.getInvoiceList(this.filterForm.value, 'invoices/get-invoice-list?page_no=' + this.page + '&page_size=' + this.pageSize).subscribe({
      next: (response: any) => {
        this.tableData = response.data.invoice_list;
        this.totalRecords = response.data.total_count;
        this.route_list = response.data.route_list;
        this.shop_list = response.data.shop_list;
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
      const newForm = new FormData();
      newForm.append('invoice_file', this.invoicePreview[0]);
      newForm.append('form', JSON.stringify(this.addForm.value));
      this.HeladivaPagesService.saveInvoice(newForm,'invoices/save-invoice',).subscribe({
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

  onEditSubmit() {
    this.submitted = true;
    if (this.editForm.invalid) {
      return;
    }
    else {
      this.spinner.show();
      this.HeladivaPagesService.editInvoice(this.editForm.value,'invoices/edit-invoice',).subscribe({
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
    this.editForm.reset();
    this.modelRef = this.modalService.open(modal, {size: 'md', centered: true, keyboard: false, backdrop: 'static'});
    this.editForm.patchValue(
      {
        invoice_ref: row.invoice_ref,
        invoice_no: row.invoice_no,
        is_active: row.is_active,
        shop_id: row.shop_id,
        route_id: row.route_id,
        date: row.date,
        amount: row.amount,
        remark: row.remark,
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
        this.spinner.show();
        this.HeladivaPagesService.deleteInvoice(row.invoice_ref, 'invoices/delete-invoice').subscribe({
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


  onPageChange = (pageNumber: number) => {
    this.page = pageNumber;
    this._fetchData();
  }

  get f() {
    return this.addForm.controls;
  }

  get f1() {
    return this.editForm.controls;
  }

  uploadInvoice(data: FileList) {
    const allowedImageTypes = [ 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

    for (let i = 0; i < data.length; i++) {
      if (allowedImageTypes.includes(data[i].type)) {
        this.invoicePreview.push(data[i]);
      } else {
        this.globalService.showError('File format is not allowed');
      }
    }
  }

  resetUploadInvoicePreview(key: number) {
    this.invoicePreview.splice(key, 1);
  }

}


