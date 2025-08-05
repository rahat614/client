export interface Medicine {
  id: string;
  name: string;
  genericName?: string;
  manufacturer: string;
  batchNumber: string;
  expiryDate: Date;
  quantity: number;
  unitPrice: number;
  category: MedicineCategory;
  minStockLevel: number;
  description?: string;
  sideEffects?: string;
  dosage: string;
  form: MedicineForm;
  prescriptionRequired: boolean;
  createdAt: Date;
  updatedAt: Date;
}

export enum MedicineCategory {
  PAIN_RELIEF = 'Pain Relief',
  ANTIBIOTICS = 'Antibiotics',
  CARDIOVASCULAR = 'Cardiovascular',
  RESPIRATORY = 'Respiratory',
  DIABETES = 'Diabetes',
  VITAMINS = 'Vitamins & Supplements',
  DIGESTIVE = 'Digestive',
  DERMATOLOGY = 'Dermatology',
  MENTAL_HEALTH = 'Mental Health',
  OTHER = 'Other'
}

export enum MedicineForm {
  TABLET = 'Tablet',
  CAPSULE = 'Capsule',
  SYRUP = 'Syrup',
  INJECTION = 'Injection',
  OINTMENT = 'Ointment',
  DROPS = 'Drops',
  INHALER = 'Inhaler',
  POWDER = 'Powder',
  OTHER = 'Other'
}

export interface StockAlert {
  id: string;
  medicineId: string;
  medicineName: string;
  alertType: AlertType;
  message: string;
  createdAt: Date;
  isRead: boolean;
}

export enum AlertType {
  LOW_STOCK = 'Low Stock',
  EXPIRED = 'Expired',
  EXPIRING_SOON = 'Expiring Soon'
}

export interface StockTransaction {
  id: string;
  medicineId: string;
  type: TransactionType;
  quantity: number;
  reason: string;
  timestamp: Date;
  userId?: string;
}

export enum TransactionType {
  STOCK_IN = 'Stock In',
  STOCK_OUT = 'Stock Out',
  ADJUSTMENT = 'Adjustment',
  EXPIRED_REMOVAL = 'Expired Removal'
}