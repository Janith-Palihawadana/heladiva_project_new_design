import { Component, OnInit } from '@angular/core';

import { alertData } from './data';
import { AlertColor } from './alerts.model';

@Component({
  selector: 'app-alerts',
  templateUrl: './alerts.component.html',
  styleUrls: ['./alerts.component.scss']
})

/**
 * Alerts Component
 */
export class AlertsComponent implements OnInit {

  // bread crumb items
  breadCrumbItems!: Array<{}>;
  alertData: AlertColor[] = [];

  constructor() { }

  ngOnInit(): void {
    /**
     * BreadCrumb Set
     */
    this.breadCrumbItems = [
      { label: 'Components' },
      { label: 'Alerts', active: true }
    ];

    /***
     * Data Get 
    */
    this._fetchData();
  }

  /***
   * Notification Data Get
   */
  private _fetchData() {
    this.alertData = alertData;
  }

  /***
   * Notification remove
   */
  close(alert: AlertColor, alertData: AlertColor[]) {
    alertData.splice(alertData.indexOf(alert), 1);
  }

}
