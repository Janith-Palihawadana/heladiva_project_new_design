import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PagesSliderComponent } from './pages-slider.component';

describe('AccountPagesSliderComponent', () => {
  let component: PagesSliderComponent;
  let fixture: ComponentFixture<PagesSliderComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PagesSliderComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PagesSliderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
