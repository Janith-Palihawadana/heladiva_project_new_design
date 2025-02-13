import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SeleRefComponent } from './sale-ref.component';

describe('SeleRefComponent', () => {
  let component: SeleRefComponent;
  let fixture: ComponentFixture<SeleRefComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SeleRefComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(SeleRefComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
