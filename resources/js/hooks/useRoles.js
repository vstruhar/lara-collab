import { usePage } from '@inertiajs/react';
import upperFirst from 'lodash/upperFirst';

const useRoles = () => {
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

  const getSelectValues = () => {
    return roles.map(role => ({value: role.name, label: upperFirst(role.name)}));
  }

  return {getColor, getSelectValues};
}

export default useRoles;
