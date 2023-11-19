import { usePage } from '@inertiajs/react';
import upperFirst from 'lodash/upperFirst';

export default function useRoles() {
  const roles = usePage().props.shared.roles;
  const roleColors = {};

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

  roles.forEach((role, index) => roleColors[role.name] = colors[index % colors.length]);


  const getColor = (role) => {
    return roleColors[role];
  };

  const getDropdownValues = ({except = []}) => {
    return roles
      .filter(i => !except.includes(i.name))
      .map(role => ({value: role.name, label: upperFirst(role.name)}));
  }

  return {getColor, getDropdownValues};
}
