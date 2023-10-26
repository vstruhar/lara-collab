import { usePage } from '@inertiajs/react';

class RoleService {
  roleColors = {};

  constructor() {
    const roles = usePage().props.roles;

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

    roles.forEach((role, index) => this.roleColors[role.name] = colors[index]);
  }

  getColor(role) {
    return this.roleColors[role];
  }
}

export default RoleService;
