import { usePage } from '@inertiajs/react';
import {upperFirst} from 'lodash';

class RoleService {
  roles = [];
  roleColors = {};

  constructor() {
    this.roles = usePage().props.roles;

    const colors = [
      'grape',
      'yellow',
      'indigo',
      'lime',
      'cyan',
      'violet',
      'orange',
      'pink',
    ];

    this.roles.forEach((role, index) => this.roleColors[role.name] = colors[index]);
  }

  getColor(role) {
    return this.roleColors[role];
  }

  getSelectValues() {
    return this.roles.map(role => ({value: role.name, label: upperFirst(role.name)}));
  }
}

export default RoleService;
