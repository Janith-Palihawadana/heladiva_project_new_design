import { MenuItem } from './menu.model';

export const MENU: MenuItem[] = [
  // {
  //   id: 1,
  //   label: 'MENUITEMS.MENU.TEXT',
  //   isTitle: true,
  //   permissions: ['6112E7','C25769'],
  // },
  {
    id: 2,
    label: 'MENUITEMS.DASHBOARDS.TEXT',
    icon: 'home',
    link: '/',
    permissions: ['6'],
  },
  {
    id: 3,
    label: 'User Role',
    icon: 'user',
    link: '/user-roles',
    permissions: ['2'],
  },
  {
    id: 4,
    label: 'Users',
    icon: 'users',
    link: '/user-list',
    permissions: ['3'],
  },
  {
    id: 5,
    label: 'Areas',
    icon: 'map',
    link: '/areas',
    permissions: ['1'],
  },
  {
    id: 6,
    label: 'Routes',
    icon: 'globe',
    link: '/routes',
    permissions: ['5'],
  },
  {
    id: 7,
    label: 'Shops',
    icon: 'shopping-cart',
    link: '/shops',
    permissions: ['4'],
  },
  {
    id: 7,
    label: 'Sale Refs',
    icon: 'tag',
    link: '/sale-ref',
    permissions: ['7'],
  },
  {
    id: 7,
    label: 'Vehicles',
    icon: 'truck',
    link: '/vehicle',
    permissions: ['8'],
  },
  {
    id: 7,
    label: 'Invoice',
    icon: 'file-text',
    link: '/invoice',
    permissions: ['8'],
  },
];

