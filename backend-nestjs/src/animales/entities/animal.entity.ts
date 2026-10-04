
import { Entity, PrimaryGeneratedColumn, Column, CreateDateColumn, UpdateDateColumn } from 'typeorm';

@Entity('animales')
export class Animal {
  @PrimaryGeneratedColumn({ name: 'id_animal' })
  id_animal: number;

  @Column({ name: 'numero_arete', unique: true })
  numero_arete: string;

  @Column({ name: 'raza_id' })
  raza_id: number;

  @Column()
  sexo: string;

  @Column({ name: 'fecha_nacimiento', type: 'date', nullable: true })
  fecha_nacimiento?: string;

  @Column({ nullable: true })
  estado?: string;

  @Column({ name: 'potrero_id' })
  potrero_id: number;

  @CreateDateColumn({ name: 'created_at' })
  created_at: Date;

  @UpdateDateColumn({ name: 'updated_at' })
  updated_at: Date;
}