import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import { h } from 'vue'

export interface Auth {
  user: User;
}

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavItem {
  title: string;
  href: string;
  icon?: LucideIcon;
  isActive?: boolean;
}

export interface SharedData extends PageProps {
  name: string;
  quote: { message: string; author: string };
  auth: Auth;
  ziggy: Config & { location: string };
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface User {
  id: number;
  name: string;
  email: string;
}

export interface CardItem {
  title: string;
  description: string;
  icon?: LucideIcon;
  count: number;
}

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from?: number;
  to?: number;
  links: PaginationLink[];
}

type UsersResponse = {
  data: User[];
  links: PaginationLink[];
  meta: PaginationMeta;
};

interface ErrorMessages {
  [key: string]: string[];
}